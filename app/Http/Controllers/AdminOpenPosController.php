<?php

namespace App\Http\Controllers;

use App\Http\Requests\OpenPosRequest;
use App\Models\Register;
use App\Support\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOpenPosController extends Controller
{
    public function index(Request $request, Workspace $workspace): View|RedirectResponse
    {
        $organization = $workspace->currentOrganization($request->user());
        abort_unless($organization, 403);

        $branches = $organization->branches()
            ->with('registers')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $registers = $branches->flatMap->registers;

        if ($branches->count() === 1 && $registers->count() === 1) {
            session([
                Workspace::BRANCH_SESSION_KEY => $branches->first()->id,
                Workspace::REGISTER_SESSION_KEY => $registers->first()->id,
            ]);

            return redirect()->route('pos.session');
        }

        return view('admin.open-pos', [
            'organization' => $organization,
            'branches' => $branches,
        ]);
    }

    public function store(OpenPosRequest $request, Workspace $workspace): RedirectResponse
    {
        $organization = $workspace->currentOrganization($request->user());
        abort_unless($organization, 403);

        $register = Register::query()
            ->whereKey($request->validated('register_id'))
            ->where('branch_id', $request->validated('branch_id'))
            ->whereHas('branch', fn ($query) => $query->whereBelongsTo($organization))
            ->firstOrFail();

        session([
            Workspace::BRANCH_SESSION_KEY => $register->branch_id,
            Workspace::REGISTER_SESSION_KEY => $register->id,
        ]);

        return redirect()->route('pos.session');
    }
}
