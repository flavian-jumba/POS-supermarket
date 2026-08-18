@props(['paymentMethod', 'customerPhone', 'total'])

<div class="pos-shadow-soft mt-1.5 rounded-xl border border-[var(--pos-border)] bg-white p-3">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-[17px] font-bold text-[var(--pos-text)]">Payment</h2>
            <p class="text-sm text-[var(--pos-text-muted)]">Select a payment method</p>
        </div>
        <div class="text-right">
            <p class="text-xs text-[var(--pos-text-muted)]">Amount Due</p>
            <p class="text-lg font-bold text-[var(--pos-orange)]">{{ \App\Support\Currency::format($total) }}</p>
        </div>
    </div>

    <div class="mt-2 grid grid-cols-10 gap-3">
        {{-- Cash --}}
        <button
            type="button"
            wire:click="selectPaymentMethod('cash')"
            @class([
                'col-span-2 flex flex-col items-center justify-center gap-0.5 rounded-xl border p-2.5 text-center transition',
                'border-[var(--pos-orange)] bg-orange-50' => $paymentMethod === 'cash',
                'border-[var(--pos-border)] bg-white hover:border-[var(--pos-text-muted)]' => $paymentMethod !== 'cash',
            ])
        >
            <x-heroicon-s-banknotes class="h-6 w-6 text-[var(--pos-success)]" />
            <span class="text-sm font-semibold text-[var(--pos-text)]">Cash</span>
            <span class="text-xs text-[var(--pos-text-muted)]">Pay with cash</span>
        </button>

        {{-- M-Pesa --}}
        <div class="col-span-5 rounded-xl border border-[var(--pos-orange)] bg-orange-50/40 p-2.5">
            <div class="flex items-center gap-2">
                <x-heroicon-s-device-phone-mobile class="h-5 w-5 text-[var(--pos-orange)]" />
                <span class="text-sm font-semibold text-[var(--pos-text)]">M-PESA STK Push</span>
            </div>

            <label for="customer-phone" class="mt-1.5 block text-xs text-[var(--pos-text-muted)]">
                Enter customer phone number
            </label>
            <div class="mt-1 flex items-center gap-2 rounded-lg border border-[var(--pos-border)] bg-white px-3 py-1.5">
                <span class="text-sm font-medium text-[var(--pos-text-secondary)]">+254</span>
                <span class="h-4 w-px bg-[var(--pos-border)]"></span>
                <input
                    id="customer-phone"
                    type="tel"
                    wire:model="customerPhone"
                    placeholder="712 345 678"
                    class="w-full border-0 p-0 text-sm text-[var(--pos-text)] placeholder:text-[var(--pos-text-muted)] focus:outline-none focus:ring-0"
                >
            </div>

            <button
                type="button"
                wire:click="sendStkPush"
                wire:loading.attr="disabled"
                class="mt-1.5 flex h-9 w-full items-center justify-center gap-2 rounded-lg bg-[var(--pos-orange)] text-sm font-semibold text-white transition hover:bg-[var(--pos-orange-hover)] disabled:opacity-60"
            >
                Send STK Push
            </button>
        </div>

        {{-- Card --}}
        <button
            type="button"
            wire:click="selectPaymentMethod('card')"
            @class([
                'col-span-3 flex flex-col items-center justify-center gap-0.5 rounded-xl border p-2.5 text-center transition',
                'border-[var(--pos-orange)] bg-orange-50' => $paymentMethod === 'card',
                'border-[var(--pos-border)] bg-white hover:border-[var(--pos-text-muted)]' => $paymentMethod !== 'card',
            ])
        >
            <x-heroicon-s-credit-card class="h-6 w-6 text-blue-500" />
            <span class="text-sm font-semibold text-[var(--pos-text)]">Card</span>
            <span class="text-xs text-[var(--pos-text-muted)]">Tap / Insert Card</span>
            <span class="my-0.5 w-full border-t border-[var(--pos-border-soft)]"></span>
            <x-heroicon-o-wifi class="h-3.5 w-3.5 text-[var(--pos-text-muted)]" />
            <span class="text-xs text-[var(--pos-text-muted)]">Tap / Insert Card</span>
        </button>
    </div>
</div>
