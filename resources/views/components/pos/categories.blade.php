@props(['categories', 'active'])

<div
    x-data="{
        overflowing: false,
        scroll(dir) { $refs.track.scrollBy({ left: dir * 160, behavior: 'smooth' }) },
        checkOverflow() { this.overflowing = $refs.track.scrollWidth > $refs.track.clientWidth + 1 },
    }"
    x-init="checkOverflow(); new ResizeObserver(() => checkOverflow()).observe($refs.track)"
    class="flex items-center gap-2"
>
    <div x-ref="track" class="pos-scrollbar-none flex flex-1 gap-2 overflow-x-auto">
        @foreach ($categories as $category)
            @php $isActive = $active === $category['key']; @endphp
            <button
                type="button"
                wire:click="selectCategory('{{ $category['key'] }}')"
                @class([
                    'flex h-10 shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full border px-4 text-sm font-medium transition',
                    'border-[var(--pos-orange)] bg-[var(--pos-orange-soft)] text-[var(--pos-orange)]' => $isActive,
                    'border-[var(--pos-border)] bg-white text-[var(--pos-text-secondary)] hover:border-[var(--pos-text-muted)]' => ! $isActive,
                ])
            >
                <x-dynamic-component :component="'heroicon-o-'.$category['icon']" class="h-4 w-4 shrink-0" />
                {{ $category['label'] }}
            </button>
        @endforeach
    </div>

    <button
        type="button"
        x-show="overflowing"
        x-cloak
        @click="scroll(1)"
        aria-label="Show more categories"
        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-[var(--pos-border)] text-[var(--pos-text-muted)] transition hover:border-[var(--pos-text-muted)]"
    >
        <x-heroicon-o-chevron-right class="h-4 w-4" />
    </button>
</div>
