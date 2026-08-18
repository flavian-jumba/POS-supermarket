<?php

namespace App\Pos;

use App\Models\Branch;
use App\Models\Organization;
use App\Models\Register;
use App\Models\RegisterSession;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PosContext
{
    /**
     * Temporary demo fallback until cashier login/session selection is wired.
     *
     * @return array{cashier:?User,organization:?Organization,branch:?Branch,register:?Register,session:?RegisterSession}
     */
    public function resolve(): array
    {
        $cashier = Auth::user() ?? User::query()->where('email', 'cashier@stanleymat.test')->first();

        $organization = $cashier?->organizations()->wherePivot('status', 'active')->first()
            ?? Organization::query()->where('slug', 'stanleymat')->first();

        $branch = $cashier?->branches()->wherePivot('status', 'active')->first()
            ?? ($organization ? Branch::query()->whereBelongsTo($organization)->where('code', 'MAIN')->first() : null);

        $register = $branch
            ? Register::query()->whereBelongsTo($branch)->where('code', 'REG-04')->first()
            : null;

        $session = $register
            ? RegisterSession::query()
                ->whereBelongsTo($register)
                ->when($cashier, fn ($query) => $query->whereBelongsTo($cashier, 'user'))
                ->where('status', 'open')
                ->latest('opened_at')
                ->first()
            : null;

        return [
            'cashier' => $cashier,
            'organization' => $organization,
            'branch' => $branch,
            'register' => $register,
            'session' => $session,
        ];
    }
}
