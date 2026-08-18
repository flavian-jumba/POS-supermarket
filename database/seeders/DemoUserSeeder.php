<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchMembership;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'stanleymat')->firstOrFail();
        $mainBranch = Branch::query()->whereBelongsTo($organization)->where('code', 'MAIN')->firstOrFail();

        $users = [
            'admin@stanleymat.test' => ['name' => 'Admin User', 'phone' => '+254722000001', 'organization_role' => 'owner', 'branch_role' => 'manager'],
            'manager@stanleymat.test' => ['name' => 'Grace Manager', 'phone' => '+254722000002', 'organization_role' => 'manager', 'branch_role' => 'manager'],
            'cashier@stanleymat.test' => ['name' => 'Faith W.', 'phone' => '+254722000003', 'organization_role' => 'cashier', 'branch_role' => 'cashier'],
        ];

        foreach ($users as $email => $attributes) {
            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $attributes['name'],
                    'phone' => $attributes['phone'],
                    'password' => 'password',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            OrganizationMembership::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'user_id' => $user->id,
                ],
                [
                    'role' => $attributes['organization_role'],
                    'status' => 'active',
                ]
            );

            BranchMembership::query()->updateOrCreate(
                [
                    'branch_id' => $mainBranch->id,
                    'user_id' => $user->id,
                ],
                [
                    'role' => $attributes['branch_role'],
                    'status' => 'active',
                ]
            );
        }
    }
}
