@props(['cashierName', 'registerStatus', 'shiftBalance'])

<button type="button" class="pos-shadow-soft mt-3 flex h-20 w-full shrink-0 items-center justify-between rounded-xl border border-[var(--pos-border)] bg-white px-6 text-left transition hover:border-[var(--pos-text-muted)]">
    <div class="flex items-center gap-3">
        <x-heroicon-o-user-circle class="h-8 w-8 text-[var(--pos-text-muted)]" />
        <div>
            <p class="text-xs text-[var(--pos-text-muted)]">Cashier</p>
            <p class="text-sm font-semibold text-[var(--pos-text)]">{{ $cashierName }}</p>
        </div>
    </div>

    <span class="h-8 w-px bg-[var(--pos-border-soft)]"></span>

    <div class="flex items-center gap-3">
        <span class="h-2.5 w-2.5 rounded-full bg-[var(--pos-success)]"></span>
        <div>
            <p class="text-xs text-[var(--pos-text-muted)]">Register Status</p>
            <p class="text-sm font-semibold text-[var(--pos-success)]">{{ $registerStatus }}</p>
        </div>
    </div>

    <span class="h-8 w-px bg-[var(--pos-border-soft)]"></span>

    <div class="flex items-center gap-3">
        <x-heroicon-o-wallet class="h-6 w-6 text-[var(--pos-text-muted)]" />
        <div>
            <p class="text-xs text-[var(--pos-text-muted)]">Shift Balance</p>
            <p class="text-sm font-semibold text-[var(--pos-text)]">{{ \App\Support\Currency::format($shiftBalance) }}</p>
        </div>
    </div>

    <x-heroicon-o-chevron-right class="h-4 w-4 text-[var(--pos-text-muted)]" />
</button>
