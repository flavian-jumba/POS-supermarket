<?php

namespace App\Pos;

use App\Models\Branch;
use App\Models\InventoryLevel;
use App\Models\MpesaTransaction;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Register;
use App\Models\RegisterSession;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    /**
     * @param  array{organizationId:?int,branchId:?int,registerId:?int,registerSessionId:?int,cashierId:?int}  $context
     * @param  array<int, array{id:int,qty:int}>  $cartItems
     */
    public function complete(array $context, array $cartItems, string $paymentMethod, ?string $paymentReference = null): Sale
    {
        if (empty($cartItems)) {
            throw new CheckoutException('Add at least one item to the sale first.');
        }

        if (! $context['organizationId'] || ! $context['branchId'] || ! $context['registerId'] || ! $context['registerSessionId'] || ! $context['cashierId']) {
            throw new CheckoutException('No complete POS context is available.');
        }

        return DB::transaction(function () use ($context, $cartItems, $paymentMethod, $paymentReference): Sale {
            Branch::query()
                ->whereKey($context['branchId'])
                ->where('organization_id', $context['organizationId'])
                ->lockForUpdate()
                ->firstOrFail();

            Register::query()
                ->whereKey($context['registerId'])
                ->where('branch_id', $context['branchId'])
                ->lockForUpdate()
                ->firstOrFail();

            RegisterSession::query()
                ->whereKey($context['registerSessionId'])
                ->where('register_id', $context['registerId'])
                ->where('user_id', $context['cashierId'])
                ->where('status', 'open')
                ->lockForUpdate()
                ->firstOrFail();

            $products = Product::query()
                ->with(['inventoryLevels' => fn ($query) => $query->where('branch_id', $context['branchId'])])
                ->where('organization_id', $context['organizationId'])
                ->whereIn('id', array_column($cartItems, 'id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0.0;

            foreach ($cartItems as $line) {
                /** @var Product|null $product */
                $product = $products->get($line['id']);

                if (! $product) {
                    throw new CheckoutException('A product in this sale is no longer available.');
                }

                if ($product->track_inventory) {
                    $onHand = (float) $product->inventoryLevels->sum('quantity_on_hand');

                    if ($onHand < $line['qty']) {
                        throw new CheckoutException("Not enough stock for {$product->name}.");
                    }
                }

                $subtotal += (float) $product->selling_price * $line['qty'];
            }

            $total = $subtotal;

            $sale = Sale::query()->create([
                'organization_id' => $context['organizationId'],
                'branch_id' => $context['branchId'],
                'register_id' => $context['registerId'],
                'register_session_id' => $context['registerSessionId'],
                'cashier_id' => $context['cashierId'],
                'sale_number' => $this->nextSaleNumber(),
                'subtotal' => $subtotal,
                'discount_total' => 0,
                'tax_total' => 0,
                'total' => $total,
                'status' => 'completed',
                'payment_status' => 'paid',
                'sold_at' => now(),
            ]);

            foreach ($cartItems as $line) {
                /** @var Product $product */
                $product = $products->get($line['id']);
                $unitPrice = (float) $product->selling_price;
                $lineTotal = $unitPrice * $line['qty'];

                SaleItem::query()->create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $line['qty'],
                    'unit_price' => $unitPrice,
                    'cost_price' => $product->cost_price,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'line_total' => $lineTotal,
                ]);

                if ($product->track_inventory) {
                    $this->deductStock($product, $context['branchId'], $context['cashierId'], $line['qty'], $sale);
                }
            }

            Payment::query()->create([
                'sale_id' => $sale->id,
                'register_session_id' => $context['registerSessionId'],
                'method' => $paymentMethod,
                'amount' => $total,
                'reference' => $paymentReference,
                'status' => 'successful',
                'paid_at' => now(),
            ]);

            if ($paymentMethod === 'cash' && $context['registerSessionId']) {
                RegisterSession::query()
                    ->whereKey($context['registerSessionId'])
                    ->increment('expected_cash', $total);
            }

            return $sale;
        });
    }

    /**
     * @param  array{organizationId:?int,branchId:?int,registerId:?int,registerSessionId:?int,cashierId:?int}  $context
     * @param  array<int, array{id:int,qty:int}>  $cartItems
     * @return array{0:Sale,1:Payment,2:MpesaTransaction}
     */
    public function createPendingMpesaPayment(array $context, array $cartItems, string $phone): array
    {
        if (empty($cartItems)) {
            throw new CheckoutException('Add at least one item to the sale first.');
        }

        if (! $context['organizationId'] || ! $context['branchId'] || ! $context['registerId'] || ! $context['registerSessionId'] || ! $context['cashierId']) {
            throw new CheckoutException('No complete POS context is available.');
        }

        return DB::transaction(function () use ($context, $cartItems, $phone): array {
            $this->validateContext($context);

            $products = $this->loadProductsForCart($context, $cartItems);
            $subtotal = $this->validateProductsAndSubtotal($products, $cartItems);

            $sale = Sale::query()->create([
                'organization_id' => $context['organizationId'],
                'branch_id' => $context['branchId'],
                'register_id' => $context['registerId'],
                'register_session_id' => $context['registerSessionId'],
                'cashier_id' => $context['cashierId'],
                'sale_number' => $this->nextSaleNumber(),
                'subtotal' => $subtotal,
                'discount_total' => 0,
                'tax_total' => 0,
                'total' => $subtotal,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'sold_at' => now(),
            ]);

            foreach ($cartItems as $line) {
                /** @var Product $product */
                $product = $products->get($line['id']);

                SaleItem::query()->create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $line['qty'],
                    'unit_price' => $product->selling_price,
                    'cost_price' => $product->cost_price,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'line_total' => (float) $product->selling_price * $line['qty'],
                ]);
            }

            $payment = Payment::query()->create([
                'sale_id' => $sale->id,
                'register_session_id' => $context['registerSessionId'],
                'method' => 'mpesa',
                'amount' => $subtotal,
                'status' => 'pending',
                'paid_at' => null,
            ]);

            $transaction = MpesaTransaction::query()->create([
                'organization_id' => $context['organizationId'],
                'sale_id' => $sale->id,
                'payment_id' => $payment->id,
                'phone' => $phone,
                'amount' => $subtotal,
                'status' => 'pending',
                'requested_at' => now(),
            ]);

            return [$sale, $payment, $transaction];
        });
    }

    public function completePendingMpesaPayment(MpesaTransaction $transaction): Sale
    {
        return DB::transaction(function () use ($transaction): Sale {
            $transaction = MpesaTransaction::query()
                ->with(['sale.items.product', 'payment'])
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            $sale = Sale::query()
                ->whereKey($transaction->sale_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($sale->payment_status === 'paid') {
                return $sale;
            }

            $this->validateContext([
                'organizationId' => $sale->organization_id,
                'branchId' => $sale->branch_id,
                'registerId' => $sale->register_id,
                'registerSessionId' => $sale->register_session_id,
                'cashierId' => $sale->cashier_id,
            ]);

            foreach ($transaction->sale->items as $item) {
                if ($item->product?->track_inventory) {
                    $this->deductStock($item->product, $sale->branch_id, $sale->cashier_id, (int) $item->quantity, $sale);
                }
            }

            $transaction->payment->update([
                'reference' => $transaction->mpesa_receipt_number,
                'status' => 'successful',
                'paid_at' => now(),
            ]);

            $sale->update([
                'status' => 'completed',
                'payment_status' => 'paid',
            ]);

            return $sale->fresh();
        });
    }

    private function deductStock(Product $product, int $branchId, ?int $cashierId, int $quantity, Sale $sale): void
    {
        $inventory = InventoryLevel::query()
            ->where('branch_id', $branchId)
            ->where('product_id', $product->id)
            ->lockForUpdate()
            ->first();

        if (! $inventory) {
            throw new CheckoutException("No inventory record for {$product->name} at this branch.");
        }

        $balanceAfter = (float) $inventory->quantity_on_hand - $quantity;
        $inventory->update(['quantity_on_hand' => $balanceAfter]);

        StockMovement::query()->create([
            'branch_id' => $branchId,
            'product_id' => $product->id,
            'user_id' => $cashierId,
            'type' => 'sale',
            'quantity' => -$quantity,
            'balance_after' => $balanceAfter,
            'reference_type' => Sale::class,
            'reference_id' => $sale->id,
        ]);
    }

    /**
     * @param  array{organizationId:?int,branchId:?int,registerId:?int,registerSessionId:?int,cashierId:?int}  $context
     */
    private function validateContext(array $context): void
    {
        Branch::query()
            ->whereKey($context['branchId'])
            ->where('organization_id', $context['organizationId'])
            ->lockForUpdate()
            ->firstOrFail();

        Register::query()
            ->whereKey($context['registerId'])
            ->where('branch_id', $context['branchId'])
            ->lockForUpdate()
            ->firstOrFail();

        RegisterSession::query()
            ->whereKey($context['registerSessionId'])
            ->where('register_id', $context['registerId'])
            ->where('user_id', $context['cashierId'])
            ->where('status', 'open')
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * @param  array{organizationId:?int,branchId:?int,registerId:?int,registerSessionId:?int,cashierId:?int}  $context
     * @param  array<int, array{id:int,qty:int}>  $cartItems
     * @return Collection<int, Product>
     */
    private function loadProductsForCart(array $context, array $cartItems): Collection
    {
        return Product::query()
            ->with(['inventoryLevels' => fn ($query) => $query->where('branch_id', $context['branchId'])])
            ->where('organization_id', $context['organizationId'])
            ->whereIn('id', array_column($cartItems, 'id'))
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    /**
     * @param  Collection<int, Product>  $products
     * @param  array<int, array{id:int,qty:int}>  $cartItems
     */
    private function validateProductsAndSubtotal(Collection $products, array $cartItems): float
    {
        $subtotal = 0.0;

        foreach ($cartItems as $line) {
            /** @var Product|null $product */
            $product = $products->get($line['id']);

            if (! $product) {
                throw new CheckoutException('A product in this sale is no longer available.');
            }

            if ($product->track_inventory) {
                $onHand = (float) $product->inventoryLevels->sum('quantity_on_hand');

                if ($onHand < $line['qty']) {
                    throw new CheckoutException("Not enough stock for {$product->name}.");
                }
            }

            $subtotal += (float) $product->selling_price * $line['qty'];
        }

        return $subtotal;
    }

    private function nextSaleNumber(): string
    {
        $prefix = 'SALE-'.now()->format('Ymd').'-';

        $count = Sale::query()->where('sale_number', 'like', $prefix.'%')->count();

        return $prefix.str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }
}
