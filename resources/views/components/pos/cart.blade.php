@props(['cart', 'cartCount', 'subtotal', 'discountTotal', 'taxTotal', 'total'])

<div class="pos-shadow-soft rounded-xl border border-[var(--pos-border)] bg-white p-3">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <x-heroicon-o-shopping-bag class="h-5 w-5 text-[var(--pos-orange)]" />
            <h2 class="text-[17px] font-bold text-[var(--pos-text)]">Current Sale</h2>
        </div>

        <div class="flex items-center gap-2">
            <span class="rounded-full bg-[var(--pos-border-soft)] px-3 py-1 text-xs font-medium text-[var(--pos-text-secondary)]">
                {{ $cartCount }} {{ Str::plural('item', $cartCount) }}
            </span>

            @if (! empty($cart))
                <button
                    type="button"
                    wire:click="clearCart"
                    wire:confirm="Clear the current sale?"
                    aria-label="Clear cart"
                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 text-[var(--pos-danger)] transition hover:bg-red-50"
                >
                    <x-heroicon-o-trash class="h-4 w-4" />
                </button>
            @endif
        </div>
    </div>

    @if (empty($cart))
        <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
            <x-heroicon-o-shopping-cart class="h-10 w-10 text-[var(--pos-text-muted)]" />
            <p class="font-medium text-[var(--pos-text)]">Your cart is empty</p>
            <p class="max-w-xs text-sm text-[var(--pos-text-muted)]">
                Scan a barcode or select a product to begin a sale.
            </p>
        </div>
    @else
        <div class="mt-2 flex text-xs font-medium uppercase tracking-wide text-[var(--pos-text-muted)]">
            <span class="w-[46%]">Item</span>
            <span class="w-[18%]">Qty</span>
            <span class="w-[15%]">Price</span>
            <span class="w-[21%]">Total</span>
        </div>

        <div class="max-h-[230px] overflow-y-auto">
            @foreach ($cart as $item)
                <x-pos.cart-item :item="$item" wire:key="cart-item-{{ $item['id'] }}" />
            @endforeach
        </div>

        <div class="mt-2 space-y-1 text-sm">
            <div class="flex justify-between text-[var(--pos-text-secondary)]">
                <span>Subtotal</span>
                <span>{{ \App\Support\Currency::format($subtotal) }}</span>
            </div>
            <div class="flex justify-between text-[var(--pos-text-secondary)]">
                <span>Discount</span>
                <span class="text-[var(--pos-orange)]">- {{ \App\Support\Currency::format($discountTotal) }}</span>
            </div>
            <div class="flex justify-between text-[var(--pos-text-secondary)]">
                <span>VAT (16%)</span>
                <span>{{ \App\Support\Currency::format($taxTotal) }}</span>
            </div>
        </div>

        <div class="my-2 border-t border-dashed border-[var(--pos-border)]"></div>

        <div class="flex items-end justify-between">
            <span class="text-base font-bold text-[var(--pos-text)]">TOTAL</span>
            <span class="text-[30px] font-bold leading-none text-[var(--pos-text)]">
                {{ \App\Support\Currency::format($total) }}
            </span>
        </div>
    @endif
</div>
