<?php

namespace App\Pos;

use App\Models\Branch;
use App\Models\Organization;
use App\Models\Register;
use App\Models\RegisterSession;
use App\Models\User;
use App\Support\Workspace;
use Illuminate\Support\Facades\Auth;

class PosContext
{
    public function __construct(private readonly Workspace $workspace) {}

    /**
     * @return array{cashier:?User,organization:?Organization,branch:?Branch,register:?Register,session:?RegisterSession}
     */
    public function resolve(): array
    {
        $cashier = Auth::user();
        $organization = $this->workspace->currentOrganization($cashier);

        if (! $organization && $cashier) {
            $organizations = $cashier->organizations()
                ->wherePivot('status', 'active')
                ->where('organizations.status', 'active')
                ->get();

            if ($organizations->count() === 1) {
                $organization = $organizations->first();
                $this->workspace->select($organization);
            }
        }

        if (! $cashier || ! $organization) {
            return $this->emptyContext($cashier, $organization);
        }

        $activeSession = RegisterSession::query()
            ->whereBelongsTo($cashier, 'user')
            ->where('status', 'open')
            ->whereHas('register.branch', fn ($query) => $query->whereBelongsTo($organization))
            ->with('register.branch')
            ->latest('opened_at')
            ->first();

        if ($activeSession && ! session(Workspace::REGISTER_SESSION_ID_KEY)) {
            session([
                Workspace::BRANCH_SESSION_KEY => $activeSession->register->branch_id,
                Workspace::REGISTER_SESSION_KEY => $activeSession->register_id,
                Workspace::REGISTER_SESSION_ID_KEY => $activeSession->id,
            ]);
        }

        $branch = Branch::query()
            ->whereKey(session(Workspace::BRANCH_SESSION_KEY))
            ->whereBelongsTo($organization)
            ->first();

        $register = $branch ? Register::query()
            ->whereKey(session(Workspace::REGISTER_SESSION_KEY))
            ->whereBelongsTo($branch)
            ->first() : null;

        $session = $register ? RegisterSession::query()
            ->whereKey(session(Workspace::REGISTER_SESSION_ID_KEY))
            ->whereBelongsTo($register)
            ->whereBelongsTo($cashier, 'user')
            ->where('status', 'open')
            ->first() : null;

        return [
            'cashier' => $cashier,
            'organization' => $organization,
            'branch' => $branch,
            'register' => $register,
            'session' => $session,
        ];
    }

    /**
     * @return array{cashier:?User,organization:?Organization,branch:null,register:null,session:null}
     */
    private function emptyContext(?User $cashier, ?Organization $organization): array
    {
        return [
            'cashier' => $cashier,
            'organization' => $organization,
            'branch' => null,
            'register' => null,
            'session' => null,
        ];
    }
}
