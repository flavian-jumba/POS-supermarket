<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Register;
use App\Models\RegisterSession;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSalesSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->where('slug', 'stanleymat')->firstOrFail();
        $branch = Branch::query()->whereBelongsTo($organization)->where('code', 'MAIN')->firstOrFail();
        $register = Register::query()->whereBelongsTo($branch)->where('code', 'REG-04')->firstOrFail();
        $cashier = User::query()->where('email', 'cashier@stanleymat.test')->firstOrFail();
        $session = RegisterSession::query()
            ->whereBelongsTo($register)
            ->whereBelongsTo($cashier, 'user')
            ->where('status', 'open')
            ->firstOrFail();

        $sale = Sale::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'sale_number' => 'SALE-DEMO-0001',
            ],
            [
                'branch_id' => $branch->id,
                'register_id' => $register->id,
                'register_session_id' => $session->id,
                'cashier_id' => $cashier->id,
                'subtotal' => 399,
                'discount_total' => 0,
                'tax_total' => 0,
                'total' => 399,
                'status' => 'completed',
                'payment_status' => 'paid',
                'sold_at' => now()->subMinutes(45),
            ]
        );

        foreach ([
            'BRK-MILK-500' => 2,
            'SUP-BREAD-400' => 1,
            'COCA-2L' => 1,
        ] as $sku => $quantity) {
            $product = Product::query()->whereBelongsTo($organization)->where('sku', $sku)->firstOrFail();
            $unitPrice = (float) $product->selling_price;

            SaleItem::query()->updateOrCreate(
                [
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                ],
                [
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'cost_price' => $product->cost_price,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'line_total' => $unitPrice * $quantity,
                ]
            );
        }

        Payment::query()->updateOrCreate(
            [
                'sale_id' => $sale->id,
                'reference' => 'DEMO-CASH-0001',
            ],
            [
                'register_session_id' => $session->id,
                'method' => 'cash',
                'amount' => 399,
                'status' => 'successful',
                'paid_at' => $sale->sold_at,
                'metadata' => ['demo' => true],
            ]
        );

        $session->update([
            'expected_cash' => (float) $session->opening_float + 399,
        ]);
    }
}
