<?php

namespace App\Http\Controllers;

use App\Support\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function index(Request $request, Workspace $workspace): View|RedirectResponse
    {
        $memberships = $request->user()->organizationMemberships()
            ->with('organization')
            ->where('status', 'active')
            ->whereHas('organization', fn ($query) => $query->where('status', 'active'))
            ->get();

        if ($memberships->count() === 1) {
            $workspace->select($memberships->first()->organization);

            return $workspace->redirectAfterSelection($request->user());
        }

        return view('workspace.index', [
            'memberships' => $memberships,
        ]);
    }

    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'integer'],
        ]);

        $organization = $request->user()->organizations()
            ->whereKey($validated['organization_id'])
            ->wherePivot('status', 'active')
            ->where('organizations.status', 'active')
            ->firstOrFail();

        $workspace->select($organization);

        return $workspace->redirectAfterSelection($request->user());
    }
}
