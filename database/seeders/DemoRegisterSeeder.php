<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Organization;
use App\Models\Register;
use App\Models\RegisterSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoRegisterSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'stanleymat')->firstOrFail();
        $mainBranch = Branch::query()->whereBelongsTo($organization)->where('code', 'MAIN')->firstOrFail();
        $cashier = User::query()->where('email', 'cashier@stanleymat.test')->firstOrFail();

        for ($number = 1; $number <= 4; $number++) {
            Register::query()->updateOrCreate(
                [
                    'branch_id' => $mainBranch->id,
                    'code' => sprintf('REG-%02d', $number),
                ],
                [
                    'name' => sprintf('Register %02d', $number),
                    'status' => 'active',
                ]
            );
        }

        $register = Register::query()
            ->whereBelongsTo($mainBranch)
            ->where('code', 'REG-04')
            ->firstOrFail();

        RegisterSession::query()->updateOrCreate(
            [
                'register_id' => $register->id,
                'user_id' => $cashier->id,
                'status' => 'open',
            ],
            [
                'opening_float' => 5000,
                'expected_cash' => 5450,
                'closing_cash' => null,
                'cash_difference' => null,
                'opened_at' => now()->subHours(2),
                'closed_at' => null,
            ]
        );
    }
}
