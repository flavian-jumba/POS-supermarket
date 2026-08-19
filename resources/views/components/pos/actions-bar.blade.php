@props(['total'])

@php
    $actions = [
        ['label' => 'Hold Sale', 'icon' => 'pause-circle'],
        ['label' => 'Discount', 'icon' => 'tag'],
        ['label' => 'Customer', 'icon' => 'user'],
        ['label' => 'Receipt', 'icon' => 'document-text'],
        ['label' => 'Park Bill', 'icon' => 'archive-box'],
        ['label' => 'Returns', 'icon' => 'arrow-uturn-left'],
    ];
@endphp

<div class="flex items-stretch gap-3">
    @foreach ($actions as $action)
        <button
            type="button"
            wire:click="placeholderAction('{{ $action['label'] }}')"
            class="flex h-12 w-24 shrink-0 flex-col items-center justify-center gap-1 rounded-xl border border-[var(--pos-border)] bg-white text-[var(--pos-text-secondary)] transition hover:border-[var(--pos-orange)] hover:text-[var(--pos-orange)]"
        >
            <x-dynamic-component :component="'heroicon-o-'.$action['icon']" class="h-5 w-5" />
            <span class="text-xs font-medium">{{ $action['label'] }}</span>
        </button>
    @endforeach

    <button
        type="button"
        wire:click="completeSale"
        class="flex h-14 flex-1 items-center justify-center gap-3 rounded-xl bg-[var(--pos-orange)] text-white transition hover:bg-[var(--pos-orange-hover)] active:scale-[0.99]"
    >
        <x-heroicon-s-shopping-cart class="h-7 w-7" />
        <span class="text-left">
            <span class="block text-base font-bold leading-tight">Complete Sale</span>
            <span class="block text-sm text-white/90">Charge {{ \App\Support\Currency::format($total) }}</span>
        </span>
    </button>
</div>
