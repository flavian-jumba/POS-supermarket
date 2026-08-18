<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class DemoOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->updateOrCreate(
            ['slug' => 'stanleymat'],
            [
                'name' => 'StanleyMat Supermarket',
                'email' => 'hello@stanleymat.test',
                'phone' => '+254700000000',
                'status' => 'active',
            ]
        );

        foreach ([
            'MAIN' => ['name' => 'Main Branch', 'phone' => '+254711000001', 'email' => 'main@stanleymat.test', 'address' => 'Moi Avenue, Nairobi'],
            'CBD' => ['name' => 'CBD Branch', 'phone' => '+254711000002', 'email' => 'cbd@stanleymat.test', 'address' => 'Kimathi Street, Nairobi CBD'],
        ] as $code => $attributes) {
            Branch::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'code' => $code,
                ],
                [
                    ...$attributes,
                    'status' => 'active',
                ]
            );
        }
    }
}
