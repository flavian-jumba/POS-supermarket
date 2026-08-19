<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranchOnboardingRequest;
use App\Http\Requests\StoreBusinessOnboardingRequest;
use App\Http\Requests\StoreRegisterOnboardingRequest;
use App\Models\Branch;
use App\Models\BranchMembership;
use App\Models\Register;
use App\Support\Workspace;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function index(Workspace $workspace): View|RedirectResponse
    {
        $organization = $workspace->currentOrganization();

        if (! $organization) {
            return redirect()->route('workspace.index');
        }

        if ($organization->onboarding_completed_at) {
            return redirect()->to(Filament::getPanel('manager')->getUrl($organization));
        }

        $branch = $organization->branches()->oldest()->first();
        $register = $branch?->registers()->oldest()->first();
        $step = session('onboarding_step', 'business');

        if ($step === 'branch' && $branch) {
            $step = 'register';
        }

        if ($step === 'register' && $register) {
            $step = 'complete';
        }

        return view('onboarding.index', [
            'organization' => $organization,
            'branch' => $branch,
            'register' => $register,
            'step' => $step,
        ]);
    }

    public function business(StoreBusinessOnboardingRequest $request, Workspace $workspace): RedirectResponse
    {
        $organization = $workspace->currentOrganization($request->user());
        abort_unless($organization, 403);

        $organization->update($request->validated());
        session(['onboarding_step' => 'branch']);

        return redirect()->route('onboarding.index');
    }

    public function branch(StoreBranchOnboardingRequest $request, Workspace $workspace): RedirectResponse
    {
        $organization = $workspace->currentOrganization($request->user());
        abort_unless($organization, 403);

        DB::transaction(function () use ($request, $organization): void {
            $branch = Branch::query()->create([
                'organization_id' => $organization->id,
                ...$request->validated(),
                'status' => 'active',
            ]);

            BranchMembership::query()->create([
                'branch_id' => $branch->id,
                'user_id' => $request->user()->id,
                'role' => 'manager',
                'status' => 'active',
            ]);
        });

        session(['onboarding_step' => 'register']);

        return redirect()->route('onboarding.index');
    }

    public function register(StoreRegisterOnboardingRequest $request, Workspace $workspace): RedirectResponse
    {
        $organization = $workspace->currentOrganization($request->user());
        $branch = $organization?->branches()->oldest()->first();
        abort_unless($organization && $branch, 403);

        DB::transaction(function () use ($request, $branch): void {
            Register::query()->create([
                'branch_id' => $branch->id,
                ...$request->validated(),
                'status' => 'active',
            ]);
        });

        session(['onboarding_step' => 'complete']);

        return redirect()->route('onboarding.index');
    }

    public function complete(Workspace $workspace): RedirectResponse
    {
        $organization = $workspace->currentOrganization();
        abort_unless($organization, 403);

        DB::transaction(function () use ($organization): void {
            $branch = $organization->branches()->oldest()->firstOrFail();
            $branch->registers()->oldest()->firstOrFail();

            $organization->update([
                'onboarding_completed_at' => now(),
            ]);
        });

        session()->forget('onboarding_step');

        return redirect()->to(Filament::getPanel('manager')->getUrl($organization));
    }
}
