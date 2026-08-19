<div class="flex h-screen flex-col overflow-hidden">
    <x-pos.header
        :organization-name="$organizationName"
        :branch-name="$branchName"
        :register-name="$registerName"
        :cashier-name="$cashierName"
    />

    @if ($notice)
        <div
            x-data
            x-init="setTimeout(() => $wire.set('notice', null), 3500)"
            @class([
                'pos-shadow-soft fixed top-20 right-8 z-50 max-w-sm rounded-xl border px-4 py-3 text-sm font-medium',
                'border-red-200 bg-red-50 text-red-600' => $noticeType === 'error',
                'border-green-200 bg-green-50 text-green-700' => $noticeType === 'success',
                'border-[var(--pos-border)] bg-white text-[var(--pos-text-secondary)]' => $noticeType === 'info',
            ])
        >
            {{ $notice }}
        </div>
    @endif

    <div class="grid min-h-0 flex-1 grid-cols-[48%_52%]">
        {{-- Left: product discovery --}}
        <section class="flex min-h-0 flex-col border-r border-[var(--pos-border-soft)] px-8 py-2.5">
            <h1 class="text-[22px] font-bold text-[var(--pos-text)]">Search products</h1>

            <div class="mt-3 flex h-[52px] items-center gap-3 rounded-xl border-2 border-[var(--pos-orange)] bg-white px-4">
                <x-heroicon-o-magnifying-glass class="h-5 w-5 text-[var(--pos-text-muted)]" />
                <input
                    type="text"
                    data-pos-scanner
                    wire:model.live.debounce.200ms="search"
                    placeholder="Scan barcode or search product..."
                    aria-label="Search products by name, SKU or barcode"
                    class="w-full border-0 bg-transparent p-0 text-[15px] text-[var(--pos-text)] placeholder:text-[var(--pos-text-muted)] focus:outline-none focus:ring-0"
                >
                <x-heroicon-o-qr-code class="h-5 w-5 text-[var(--pos-text-muted)]" />
            </div>

            <h2 class="mt-5 text-sm font-semibold text-[var(--pos-text-secondary)]">Categories</h2>
            <div class="mt-2">
                <x-pos.categories :categories="$this->categories" :active="$activeCategory" />
            </div>

            <div class="mt-5 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-[var(--pos-text-secondary)]">Quick Access</h2>
                <button type="button" wire:click="placeholderAction('View all')" class="text-sm font-medium text-[var(--pos-orange)]">
                    View all &gt;
                </button>
            </div>

            <div class="mt-3 min-h-0 flex-1 overflow-y-auto pr-1">
                @if (empty($this->products))
                    <div class="flex flex-col items-center justify-center gap-2 py-14 text-center">
                        <x-heroicon-o-magnifying-glass class="h-8 w-8 text-[var(--pos-text-muted)]" />
                        <p class="font-medium text-[var(--pos-text)]">No products found</p>
                        <p class="text-sm text-[var(--pos-text-muted)]">Try another product name, SKU or barcode.</p>
                    </div>
                @else
                    <div class="grid grid-cols-3 gap-4">
                        @foreach ($this->products as $product)
                            <x-pos.product-card :product="$product" wire:key="product-{{ $product['id'] }}" />
                        @endforeach
                    </div>
                @endif
            </div>

            <x-pos.cashier-status
                :cashier-name="$cashierName"
                :register-status="$registerStatus"
                :shift-balance="$shiftBalance"
            />
        </section>

        {{-- Right: current sale + payment --}}
        <section class="flex min-h-0 flex-col justify-between gap-4 overflow-y-auto px-8 py-2.5">
            <x-pos.cart
                :cart="$cart"
                :cart-count="$this->cartCount"
                :subtotal="$this->subtotal"
                :discount-total="$this->discountTotal"
                :tax-total="$this->taxTotal"
                :total="$this->total"
            />

            <x-pos.payment-panel
                :payment-method="$paymentMethod"
                :customer-phone="$customerPhone"
                :mpesa-status="$mpesaStatus"
                :mpesa-reference="$mpesaReference"
                :mpesa-available="$mpesaAvailable"
                :total="$this->total"
            />

            <x-pos.actions-bar :total="$this->total" />
        </section>
    </div>
</div>
