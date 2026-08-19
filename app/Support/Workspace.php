<?php

namespace App\Support;

use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;

class Workspace
{
    public const ORGANIZATION_SESSION_KEY = 'current_organization_id';

    public const BRANCH_SESSION_KEY = 'current_branch_id';

    public const REGISTER_SESSION_KEY = 'current_register_id';

    public const REGISTER_SESSION_ID_KEY = 'current_register_session_id';

    public function currentOrganization(?User $user = null): ?Organization
    {
        $user ??= auth()->user();

        if (! $user) {
            return null;
        }

        $organizationId = session(self::ORGANIZATION_SESSION_KEY);

        if (! $organizationId) {
            return null;
        }

        return $user->organizations()
            ->whereKey($organizationId)
            ->wherePivot('status', 'active')
            ->where('organizations.status', 'active')
            ->first();
    }

    public function currentMembership(?User $user = null): ?OrganizationMembership
    {
        $user ??= auth()->user();
        $organization = $this->currentOrganization($user);

        if (! $user || ! $organization) {
            return null;
        }

        return $user->organizationMemberships()
            ->whereBelongsTo($organization)
            ->where('status', 'active')
            ->first();
    }

    public function select(Organization $organization): void
    {
        session([
            self::ORGANIZATION_SESSION_KEY => $organization->id,
        ]);

        session()->forget([
            self::BRANCH_SESSION_KEY,
            self::REGISTER_SESSION_KEY,
            self::REGISTER_SESSION_ID_KEY,
        ]);
    }

    public function clearPos(): void
    {
        session()->forget([
            self::BRANCH_SESSION_KEY,
            self::REGISTER_SESSION_KEY,
            self::REGISTER_SESSION_ID_KEY,
        ]);
    }

    public function redirectAfterSelection(User $user): RedirectResponse
    {
        $membership = $this->currentMembership($user);
        $organization = $this->currentOrganization($user);

        if (! $membership || ! $organization) {
            return redirect()->route('workspace.index');
        }

        if ($membership->isAdminRole()) {
            if (! $organization->onboarding_completed_at) {
                return redirect()->route('onboarding.index');
            }

            return redirect()->to(Filament::getPanel('manager')->getUrl($organization));
        }

        return redirect()->route('pos.session');
    }

    public function redirectAfterLogin(User $user): RedirectResponse
    {
        $memberships = $user->organizationMemberships()
            ->with('organization')
            ->where('status', 'active')
            ->whereHas('organization', fn ($query) => $query->where('status', 'active'))
            ->get();

        if ($memberships->count() !== 1) {
            return redirect()->route('workspace.index');
        }

        $this->select($memberships->first()->organization);

        return $this->redirectAfterSelection($user);
    }
}
