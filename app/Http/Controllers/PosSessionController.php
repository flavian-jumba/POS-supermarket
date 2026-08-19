<?php

namespace App\Http\Controllers;

use App\Http\Requests\StartRegisterSessionRequest;
use App\Models\Register;
use App\Models\RegisterSession;
use App\Support\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosSessionController extends Controller
{
    public function index(Request $request, Workspace $workspace): View|RedirectResponse
    {
        $organization = $workspace->currentOrganization($request->user());

        if (! $organization) {
            return redirect()->route('workspace.index');
        }

        $activeSession = RegisterSession::query()
            ->whereBelongsTo($request->user(), 'user')
            ->where('status', 'open')
            ->whereHas('register.branch', fn ($query) => $query->whereBelongsTo($organization))
            ->with('register.branch')
            ->latest('opened_at')
            ->first();

        if ($activeSession) {
            $this->storeContext($activeSession);

            return redirect()->route('pos');
        }

        $registers = Register::query()
            ->with('branch')
            ->where('status', 'active')
            ->whereHas('branch', function ($query) use ($organization, $request): void {
                $query
                    ->whereBelongsTo($organization)
                    ->where(function ($query) use ($request): void {
                        $query
                            ->whereHas('memberships', fn ($query) => $query->whereBelongsTo($request->user())->where('status', 'active'))
                            ->orWhereHas('organization.memberships', fn ($query) => $query->whereBelongsTo($request->user())->where('status', 'active')->whereIn('role', ['owner', 'admin', 'manager']));
                    });
            })
            ->orderBy('name')
            ->get();

        return view('pos.session', [
            'organization' => $organization,
            'registers' => $registers,
        ]);
    }

    public function store(StartRegisterSessionRequest $request, Workspace $workspace): RedirectResponse
    {
        $organization = $workspace->currentOrganization($request->user());
        abort_unless($organization, 403);

        $register = Register::query()
            ->with('branch')
            ->whereKey($request->validated('register_id'))
            ->whereHas('branch', function ($query) use ($organization, $request): void {
                $query
                    ->whereBelongsTo($organization)
                    ->where(function ($query) use ($request): void {
                        $query
                            ->whereHas('memberships', fn ($query) => $query->whereBelongsTo($request->user())->where('status', 'active'))
                            ->orWhereHas('organization.memberships', fn ($query) => $query->whereBelongsTo($request->user())->where('status', 'active')->whereIn('role', ['owner', 'admin', 'manager']));
                    });
            })
            ->firstOrFail();

        $session = DB::transaction(function () use ($request, $register): RegisterSession {
            return RegisterSession::query()->create([
                'register_id' => $register->id,
                'user_id' => $request->user()->id,
                'opening_float' => $request->validated('opening_cash'),
                'expected_cash' => $request->validated('opening_cash'),
                'opened_at' => now(),
                'status' => 'open',
            ]);
        });

        $this->storeContext($session->load('register.branch'));

        return redirect()->route('pos');
    }

    private function storeContext(RegisterSession $session): void
    {
        session([
            Workspace::BRANCH_SESSION_KEY => $session->register->branch_id,
            Workspace::REGISTER_SESSION_KEY => $session->register_id,
            Workspace::REGISTER_SESSION_ID_KEY => $session->id,
        ]);
    }
}
