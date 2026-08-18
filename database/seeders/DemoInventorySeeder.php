<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\InventoryLevel;
use App\Models\Organization;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoInventorySeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'stanleymat')->firstOrFail();
        $mainBranch = Branch::query()->whereBelongsTo($organization)->where('code', 'MAIN')->firstOrFail();
        $cbdBranch = Branch::query()->whereBelongsTo($organization)->where('code', 'CBD')->firstOrFail();
        $admin = User::query()->where('email', 'admin@stanleymat.test')->first();

        $mainQuantities = [
            'BRK-MILK-500' => 84,
            'SUP-BREAD-400' => 55,
            'COCA-2L' => 42,
            'ORG-JUICE-1L' => 28,
            'POT-CRISPS-50' => 70,
            'MULTI-CLEAN-750' => 19,
            'TOM-KG' => 45,
            'BAN-KG' => 35,
            'UNGA-2KG' => 40,
            'SUGAR-2KG' => 26,
            'OIL-1L' => 22,
            'WATER-1L' => 96,
        ];

        foreach (Product::query()->whereBelongsTo($organization)->get() as $product) {
            $quantity = $mainQuantities[$product->sku] ?? 20;

            $this->seedInventoryLevel($mainBranch, $product, $quantity);
            $this->seedOpeningMovement($mainBranch, $product, $quantity, $admin);

            $cbdQuantity = max(6, (int) floor($quantity / 2));
            $this->seedInventoryLevel($cbdBranch, $product, $cbdQuantity);
            $this->seedOpeningMovement($cbdBranch, $product, $cbdQuantity, $admin);
        }
    }

    protected function seedInventoryLevel(Branch $branch, Product $product, int $quantity): void
    {
        InventoryLevel::query()->updateOrCreate(
            [
                'branch_id' => $branch->id,
                'product_id' => $product->id,
            ],
            [
                'quantity_on_hand' => $quantity,
                'reorder_level' => $product->minimum_stock_level,
            ]
        );
    }

    protected function seedOpeningMovement(Branch $branch, Product $product, int $quantity, ?User $user): void
    {
        StockMovement::query()->updateOrCreate(
            [
                'branch_id' => $branch->id,
                'product_id' => $product->id,
                'type' => 'opening_stock',
                'reference_type' => null,
                'reference_id' => null,
            ],
            [
                'user_id' => $user?->id,
                'quantity' => $quantity,
                'balance_after' => $quantity,
                'unit_cost' => $product->cost_price,
                'notes' => 'Demo opening stock',
            ]
        );
    }
}
