@props(['paymentMethod', 'customerPhone', 'mpesaStatus', 'mpesaReference', 'mpesaAvailable', 'total'])

<div class="pos-shadow-soft rounded-xl border border-[var(--pos-border)] bg-white p-4">
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

    <div class="mt-3 grid grid-cols-3 items-stretch gap-3">
        {{-- Cash --}}
        <button
            type="button"
            wire:click="selectPaymentMethod('cash')"
            @class([
                'flex flex-col items-center justify-center gap-1 rounded-xl border p-4 text-center transition',
                'border-[var(--pos-orange)] bg-[var(--pos-orange-soft)]' => $paymentMethod === 'cash',
                'border-[var(--pos-border)] bg-white hover:border-[var(--pos-text-muted)]' => $paymentMethod !== 'cash',
            ])
        >
            <x-heroicon-s-banknotes class="h-6 w-6 text-[var(--pos-success)]" />
            <span class="text-sm font-semibold text-[var(--pos-text)]">Cash</span>
            <span class="text-xs text-[var(--pos-text-muted)]">Pay with cash</span>
        </button>

        {{-- M-Pesa --}}
        <div @class([
            'flex flex-col rounded-xl border p-4 transition',
            'border-[var(--pos-orange)] bg-[var(--pos-orange-soft)]' => $paymentMethod === 'mpesa',
            'border-[var(--pos-border)] bg-white' => $paymentMethod !== 'mpesa',
            'opacity-70' => ! $mpesaAvailable,
        ])>
            <div class="flex items-center gap-2">
                <x-heroicon-s-device-phone-mobile class="h-5 w-5 text-[var(--pos-orange)]" />
                <span class="text-sm font-semibold text-[var(--pos-text)]">M-PESA STK Push</span>
            </div>

            @unless ($mpesaAvailable)
                <div class="mt-2 rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-[var(--pos-danger)]">
                    M-Pesa is not configured for this supermarket.
                </div>
            @endunless

            <div class="mt-2 flex items-center gap-2 rounded-lg border border-[var(--pos-border)] bg-white px-3 py-1.5">
                <span class="text-sm font-medium text-[var(--pos-text-secondary)]">+254</span>
                <span class="h-4 w-px bg-[var(--pos-border)]"></span>
                <input
                    id="customer-phone"
                    type="tel"
                    wire:model="customerPhone"
                    placeholder="712 345 678"
                    aria-label="Customer M-Pesa phone number"
                    class="w-full border-0 p-0 text-sm text-[var(--pos-text)] placeholder:text-[var(--pos-text-muted)] focus:outline-none focus:ring-0"
                >
            </div>

            <button
                type="button"
                wire:click="sendStkPush"
                wire:loading.attr="disabled"
                wire:target="sendStkPush"
                @disabled(! $mpesaAvailable || in_array($mpesaStatus, ['sending', 'waiting', 'processing'], true))
                class="mt-2 flex h-9 w-full items-center justify-center gap-2 rounded-lg bg-[var(--pos-orange)] text-sm font-semibold text-white transition hover:bg-[var(--pos-orange-hover)] disabled:opacity-60"
            >
                {{ $mpesaStatus === 'sending' ? 'Sending request...' : 'Send STK Push' }}
            </button>

            @if ($mpesaStatus !== 'idle')
                <div @class([
                    'mt-2 flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium',
                    'bg-orange-50 text-[var(--pos-orange)]' => in_array($mpesaStatus, ['sending', 'waiting', 'processing']),
                    'bg-green-50 text-[var(--pos-success)]' => $mpesaStatus === 'successful',
                    'bg-red-50 text-[var(--pos-danger)]' => in_array($mpesaStatus, ['failed', 'cancelled', 'timeout']),
                ])>
                    @if ($mpesaStatus === 'sending')
                        <span class="h-2 w-2 animate-pulse rounded-full bg-[var(--pos-orange)]"></span>
                        <span>Sending request...</span>
                    @elseif (in_array($mpesaStatus, ['waiting', 'processing'], true))
                        <span class="h-2 w-2 animate-pulse rounded-full bg-[var(--pos-orange)]"></span>
                        <span>Waiting for customer to enter M-Pesa PIN...</span>
                    @elseif ($mpesaStatus === 'successful')
                        <x-heroicon-s-check-circle class="h-4 w-4" />
                        <span>Payment received — {{ $mpesaReference }}</span>
                    @elseif (in_array($mpesaStatus, ['failed', 'cancelled', 'timeout'], true))
                        <x-heroicon-s-x-circle class="h-4 w-4" />
                        <span>{{ ucfirst($mpesaStatus) }} — try again</span>
                    @endif
                </div>

                @if (in_array($mpesaStatus, ['waiting', 'processing'], true))
                    <button
                        type="button"
                        wire:click="checkMpesaStatus"
                        class="mt-2 text-xs font-bold text-[var(--pos-orange)] hover:text-[var(--pos-orange-hover)]"
                    >
                        Check status
                    </button>
                @endif
            @endif
        </div>

        {{-- Card --}}
        <button
            type="button"
            wire:click="selectPaymentMethod('card')"
            @class([
                'flex flex-col items-center justify-center gap-1 rounded-xl border p-4 text-center transition',
                'border-[var(--pos-orange)] bg-[var(--pos-orange-soft)]' => $paymentMethod === 'card',
                'border-[var(--pos-border)] bg-white hover:border-[var(--pos-text-muted)]' => $paymentMethod !== 'card',
            ])
        >
            <x-heroicon-s-credit-card class="h-6 w-6 text-blue-500" />
            <span class="text-sm font-semibold text-[var(--pos-text)]">Card</span>
            <span class="text-xs text-[var(--pos-text-muted)]">Tap / insert / swipe</span>
        </button>
    </div>
</div>
