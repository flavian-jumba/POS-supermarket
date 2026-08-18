@props(['product'])

<div {{ $attributes->class([
    'pos-shadow-soft group relative flex h-[188px] flex-col justify-between rounded-xl border border-[var(--pos-border)] bg-white p-3.5 transition',
    'hover:border-[var(--pos-text-muted)]' => $product['isAvailable'],
    'opacity-60' => ! $product['isAvailable'],
]) }}>
    <div class="flex items-start justify-between">
        <button type="button" aria-label="Add {{ $product['name'] }} to favorites" class="text-[var(--pos-text-muted)] transition hover:text-[var(--pos-orange)]">
            <x-heroicon-o-star class="h-4.5 w-4.5" />
        </button>

        <span @class([
            'rounded-full px-2 py-0.5 text-[10px] font-semibold',
            'bg-green-50 text-[var(--pos-success)]' => $product['isAvailable'],
            'bg-red-50 text-[var(--pos-danger)]' => ! $product['isAvailable'],
        ])>
            {{ $product['isAvailable'] ? $product['stockLabel'] : 'Out of stock' }}
        </span>
    </div>

    <div class="flex flex-1 items-center justify-center">
        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-[#fff5ec]">
            <x-pos.illustration :type="$product['illustration']" class="h-10 w-10" />
        </div>
    </div>

    <div class="flex items-end justify-between">
        <div>
            <p class="text-sm font-semibold text-[var(--pos-text)]">{{ $product['name'] }}</p>
            <p class="text-xs text-[var(--pos-text-muted)]">{{ $product['sku'] }}</p>
            <p class="text-sm font-medium text-[var(--pos-orange)]">
                @if ($product['priceFrom'])
                    From {{ \App\Support\Currency::format($product['price']) }}
                @else
                    {{ \App\Support\Currency::format($product['price']) }}
                @endif
            </p>
        </div>

        <button
            type="button"
            wire:click="addToCart({{ $product['id'] }})"
            aria-label="Add {{ $product['name'] }} to cart"
            @disabled(! $product['isAvailable'])
            @class([
                'flex h-9 w-9 items-center justify-center rounded-full text-white transition',
                'bg-[var(--pos-orange)] hover:bg-[var(--pos-orange-hover)]' => $product['isAvailable'],
                'cursor-not-allowed bg-[var(--pos-text-muted)]' => ! $product['isAvailable'],
            ])
        >
            <x-heroicon-o-plus class="h-5 w-5" />
        </button>
    </div>
</div>
