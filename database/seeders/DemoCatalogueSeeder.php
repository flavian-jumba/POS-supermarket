<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Organization;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'stanleymat')->firstOrFail();

        $categories = collect([
            'Fresh Produce',
            'Bakery',
            'Beverages',
            'Dairy',
            'Snacks',
            'Home Care',
        ])->mapWithKeys(fn (string $name): array => [
            $name => Category::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'slug' => Str::slug($name),
                ],
                [
                    'name' => $name,
                    'description' => null,
                    'is_active' => true,
                ]
            ),
        ]);

        $units = collect([
            'Piece' => 'pcs',
            'Bottle' => 'btl',
            'Packet' => 'pkt',
            'Kilogram' => 'kg',
            'Litre' => 'L',
        ])->mapWithKeys(fn (string $symbol, string $name): array => [
            $name => Unit::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'symbol' => $symbol,
                ],
                ['name' => $name]
            ),
        ]);

        $products = [
            ['Brookside Milk 500ml', 'BRK-MILK-500', 'Dairy', 'Bottle', 58.00, 72.00, 12, '890100000001'],
            ['Superloaf Bread', 'SUP-BREAD-400', 'Bakery', 'Piece', 48.00, 65.00, 10, '890100000002'],
            ['Coca-Cola 2L', 'COCA-2L', 'Beverages', 'Bottle', 150.00, 190.00, 8, '890100000003'],
            ['Orange Juice', 'ORG-JUICE-1L', 'Beverages', 'Litre', 115.00, 145.00, 8, '890100000004'],
            ['Potato Crisps', 'POT-CRISPS-50', 'Snacks', 'Packet', 45.00, 60.00, 15, '890100000005'],
            ['Multipurpose Cleaner', 'MULTI-CLEAN-750', 'Home Care', 'Bottle', 185.00, 240.00, 6, '890100000006'],
            ['Tomatoes', 'TOM-KG', 'Fresh Produce', 'Kilogram', 85.00, 120.00, 10, '890100000007'],
            ['Bananas', 'BAN-KG', 'Fresh Produce', 'Kilogram', 95.00, 135.00, 10, '890100000008'],
            ['Unga Maize Flour 2kg', 'UNGA-2KG', 'Bakery', 'Packet', 145.00, 180.00, 10, '890100000009'],
            ['Sugar 2kg', 'SUGAR-2KG', 'Snacks', 'Packet', 220.00, 260.00, 10, '890100000010'],
            ['Cooking Oil 1L', 'OIL-1L', 'Home Care', 'Bottle', 275.00, 330.00, 8, '890100000011'],
            ['Mineral Water 1L', 'WATER-1L', 'Beverages', 'Bottle', 55.00, 80.00, 12, '890100000012'],
        ];

        foreach ($products as [$name, $sku, $category, $unit, $costPrice, $sellingPrice, $minimumStockLevel, $barcode]) {
            $product = Product::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'sku' => $sku,
                ],
                [
                    'category_id' => $categories[$category]->id,
                    'unit_id' => $units[$unit]->id,
                    'name' => $name,
                    'description' => null,
                    'image_path' => null,
                    'cost_price' => $costPrice,
                    'selling_price' => $sellingPrice,
                    'track_inventory' => true,
                    'minimum_stock_level' => $minimumStockLevel,
                    'is_active' => true,
                ]
            );

            ProductBarcode::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'barcode' => $barcode,
                ],
                [
                    'product_id' => $product->id,
                    'is_primary' => true,
                ]
            );
        }
    }
}
