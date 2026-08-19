@props(['organizationName', 'branchName', 'registerName', 'cashierName'])

<header class="flex h-16 shrink-0 items-center justify-between border-b border-[var(--pos-border)] bg-[var(--pos-surface)] px-8">
    <div class="flex items-center gap-3">
        <span class="h-2.5 w-2.5 rounded-full bg-[var(--pos-orange)]"></span>
        <x-heroicon-s-star class="h-5 w-5 text-[var(--pos-orange)]" />
        <span class="text-[19px] font-bold tracking-tight text-[var(--pos-text)]">{{ $organizationName }}</span>

        <span class="mx-2 h-5 w-px bg-[var(--pos-border)]"></span>

        <x-heroicon-s-map-pin class="h-4 w-4 text-[var(--pos-orange)]" />
        <span class="text-sm font-medium text-[var(--pos-text-secondary)]">{{ $branchName }}</span>
    </div>

    <div class="flex items-center gap-3 text-sm">
        <span class="font-medium text-[var(--pos-text-secondary)]">{{ $registerName }}</span>
        <span class="h-5 w-px bg-[var(--pos-border)]"></span>
        <x-heroicon-o-clock class="h-4 w-4 text-[var(--pos-text-muted)]" />
        <span data-pos-clock class="font-medium text-[var(--pos-text-secondary)]" aria-live="polite">&nbsp;</span>
        <span class="h-5 w-px bg-[var(--pos-border)]"></span>
        <button
            type="button"
            aria-label="Cashier profile: {{ $cashierName }}"
            class="flex h-9 items-center gap-2 rounded-full border border-[var(--pos-orange)] pr-3 pl-2 text-[var(--pos-orange)] transition hover:bg-[var(--pos-orange-soft)]"
        >
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[var(--pos-orange-soft)] text-xs font-bold">
                {{ Illuminate\Support\Str::of($cashierName)->substr(0, 1)->upper() }}
            </span>
            <span class="max-w-[9rem] truncate text-sm font-medium">{{ $cashierName }}</span>
        </button>
    </div>
</header>
