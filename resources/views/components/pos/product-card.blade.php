@props(['product'])

@php
    $badge = match ($product['stockState']) {
        'low_stock' => ['bg-amber-50 text-amber-600', 'Low stock'],
        'out_of_stock' => ['bg-red-50 text-[var(--pos-danger)]', 'Out of stock'],
        default => ['bg-green-50 text-[var(--pos-success)]', $product['stockLabel']],
    };
@endphp

<div {{ $attributes->class([
    'pos-shadow-soft group relative flex h-[212px] flex-col justify-between rounded-xl border border-[var(--pos-border)] bg-white p-4 transition',
    'hover:border-[var(--pos-text-muted)]' => $product['isAvailable'],
    'opacity-60' => ! $product['isAvailable'],
]) }}>
    <div class="flex items-start justify-between gap-2">
        <button type="button" aria-label="Add {{ $product['name'] }} to favorites" class="text-[var(--pos-text-muted)] transition hover:text-[var(--pos-orange)]">
            <x-heroicon-o-star class="h-4.5 w-4.5" />
        </button>

        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $badge[0] }}">
            {{ $badge[1] }}
        </span>
    </div>

    <div class="flex flex-1 items-center justify-center">
        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-[var(--pos-orange-soft)]">
            <x-pos.illustration :type="$product['illustration']" class="h-10 w-10" />
        </div>
    </div>

    <div class="flex items-end justify-between gap-2">
        <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-[var(--pos-text)]">{{ $product['name'] }}</p>
            <p class="text-xs text-[var(--pos-text-muted)]">{{ $product['sku'] }}</p>
            <p class="text-sm font-medium text-[var(--pos-orange)]">
                {{ \App\Support\Currency::format($product['price']) }}
            </p>
        </div>

        <button
            type="button"
            wire:click="addToCart({{ $product['id'] }})"
            aria-label="Add {{ $product['name'] }} to cart"
            @disabled(! $product['isAvailable'])
            @class([
                'flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-white transition',
                'bg-[var(--pos-orange)] hover:bg-[var(--pos-orange-hover)]' => $product['isAvailable'],
                'cursor-not-allowed bg-[var(--pos-text-muted)]' => ! $product['isAvailable'],
            ])
        >
            <x-heroicon-o-plus class="h-5 w-5" />
        </button>
    </div>
</div>
