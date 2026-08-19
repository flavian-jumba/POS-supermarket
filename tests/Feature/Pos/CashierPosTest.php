<?php

use App\Livewire\Pos\CashierPos;
use App\Models\InventoryLevel;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->cashier = User::query()->where('email', 'cashier@stanleymat.test')->firstOrFail();
    $this->actingAs($this->cashier);
});

test('add to cart increases the total', function () {
    $product = Product::query()->where('sku', 'BRK-MILK-500')->firstOrFail();

    Livewire::test(CashierPos::class)
        ->call('addToCart', $product->id)
        ->assertSet('cart.'.$product->id.'.qty', 1)
        ->assertSee((string) $product->name);
});

test('an out of stock tracked product cannot be added', function () {
    $product = Product::query()->where('sku', 'BRK-MILK-500')->firstOrFail();
    InventoryLevel::query()->where('product_id', $product->id)->update(['quantity_on_hand' => 0]);

    Livewire::test(CashierPos::class)
        ->call('addToCart', $product->id)
        ->assertSet('cart', []);
});

test('category filter narrows the product list', function () {
    $product = Product::query()->where('sku', 'BRK-MILK-500')->with('category')->firstOrFail();

    $component = Livewire::test(CashierPos::class)
        ->call('selectCategory', (string) $product->category_id);

    $ids = collect($component->get('products'))->pluck('id');

    expect($ids)->toContain($product->id);
    expect($ids->count())->toBeLessThanOrEqual(Product::query()->where('category_id', $product->category_id)->count());
});

test('searching by barcode finds the product', function () {
    $product = Product::query()->where('sku', 'COCA-2L')->with('barcodes')->firstOrFail();
    $barcode = $product->barcodes->first()->barcode;

    $component = Livewire::test(CashierPos::class)
        ->set('search', $barcode);

    $ids = collect($component->get('products'))->pluck('id');

    expect($ids)->toContain($product->id);
});

test('completing a cash sale creates real records and deducts stock', function () {
    $product = Product::query()->where('sku', 'BRK-MILK-500')->firstOrFail();
    $before = (float) InventoryLevel::query()->where('product_id', $product->id)->firstOrFail()->quantity_on_hand;

    Livewire::test(CashierPos::class)
        ->call('addToCart', $product->id)
        ->call('selectPaymentMethod', 'cash')
        ->call('completeSale')
        ->assertSet('cart', []);

    $sale = Sale::query()->latest('id')->firstOrFail();
    expect($sale->items)->toHaveCount(1);
    expect((float) $sale->total)->toBe((float) $product->selling_price);

    $payment = Payment::query()->where('sale_id', $sale->id)->firstOrFail();
    expect($payment->method)->toBe('cash');
    expect($payment->status)->toBe('successful');

    $movement = StockMovement::query()->where('reference_id', $sale->id)->where('reference_type', Sale::class)->firstOrFail();
    expect($movement->type)->toBe('sale');

    $after = (float) InventoryLevel::query()->where('product_id', $product->id)->firstOrFail()->quantity_on_hand;
    expect($after)->toBe($before - 1);
});

test('completing a sale is blocked until mpesa confirms', function () {
    $product = Product::query()->where('sku', 'BRK-MILK-500')->firstOrFail();

    Livewire::test(CashierPos::class)
        ->call('addToCart', $product->id)
        ->set('paymentMethod', 'mpesa')
        ->set('mpesaStatus', 'waiting')
        ->call('completeSale')
        ->assertSet('cart.'.$product->id.'.qty', 1);

    expect(Sale::query()->count())->toBe(1);
});
