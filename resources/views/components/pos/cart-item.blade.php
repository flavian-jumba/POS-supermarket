@props(['item'])

<div {{ $attributes->class(['flex h-16 items-center gap-2 px-1']) }}>
    <div class="flex w-[44%] items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[var(--pos-orange-soft)]">
            <x-pos.illustration :type="$item['illustration']" class="h-6 w-6" />
        </div>
        <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-[var(--pos-text)]">{{ $item['name'] }}</p>
            <p class="text-xs text-[var(--pos-text-muted)]">{{ $item['sku'] }}</p>
        </div>
    </div>

    <div class="flex w-[18%] justify-start">
        <div class="inline-flex h-8 items-center rounded-full border border-[var(--pos-border)] text-sm">
            <button
                type="button"
                wire:click="decrementItem({{ $item['id'] }})"
                aria-label="Decrease quantity of {{ $item['name'] }}"
                class="flex h-8 w-8 items-center justify-center rounded-l-full text-[var(--pos-text-secondary)] transition hover:bg-[var(--pos-border-soft)] hover:text-[var(--pos-orange)]"
            >
                <x-heroicon-o-minus class="h-3.5 w-3.5" />
            </button>
            <span class="w-6 text-center font-medium">{{ $item['qty'] }}</span>
            <button
                type="button"
                wire:click="incrementItem({{ $item['id'] }})"
                aria-label="Increase quantity of {{ $item['name'] }}"
                class="flex h-8 w-8 items-center justify-center rounded-r-full text-[var(--pos-text-secondary)] transition hover:bg-[var(--pos-border-soft)] hover:text-[var(--pos-orange)]"
            >
                <x-heroicon-o-plus class="h-3.5 w-3.5" />
            </button>
        </div>
    </div>

    <div class="w-[14%] text-sm text-[var(--pos-text-secondary)]">
        {{ \App\Support\Currency::format($item['price']) }}
    </div>

    <div class="w-[16%] text-sm font-semibold text-[var(--pos-text)]">
        {{ \App\Support\Currency::format($item['price'] * $item['qty']) }}
    </div>

    <div class="flex w-[8%] justify-end">
        <div x-data="{ open: false }" class="relative">
            <button
                type="button"
                @click="open = !open"
                @click.outside="open = false"
                aria-label="More actions for {{ $item['name'] }}"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-[var(--pos-text-muted)] transition hover:bg-[var(--pos-border-soft)]"
            >
                <x-heroicon-o-ellipsis-vertical class="h-4 w-4" />
            </button>

            <div
                x-show="open"
                x-cloak
                x-transition
                class="absolute right-0 z-10 mt-1 w-36 rounded-lg border border-[var(--pos-border)] bg-white p-1 pos-shadow-soft"
            >
                <button
                    type="button"
                    wire:click="removeItem({{ $item['id'] }})"
                    class="w-full rounded-md px-2 py-1.5 text-left text-sm text-[var(--pos-danger)] transition hover:bg-red-50"
                >
                    Remove item
                </button>
            </div>
        </div>
    </div>
</div>
