<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DemoOrganizationSeeder::class,
            DemoUserSeeder::class,
            DemoCatalogueSeeder::class,
            DemoInventorySeeder::class,
            DemoRegisterSeeder::class,
            DemoSalesSeeder::class,
        ]);
    }
}
