<x-filament-panels::page>
    @php($integration = $this->integration())

    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">M-PESA Integration</x-slot>
            <x-slot name="description">Status and connection summary for this supermarket.</x-slot>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</div>
                    <div class="mt-2">
                        <x-filament::badge :color="$this->statusColor()">
                            {{ $this->statusLabel() }}
                        </x-filament::badge>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Environment</div>
                    <div class="mt-2">
                        <x-filament::badge :color="$this->environmentColor()">
                            {{ $this->environmentLabel() }}
                        </x-filament::badge>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Account</div>
                    <div class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">
                        {{ $this->maskedAccount() }}
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Verified</div>
                    <div class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">
                        {{ $integration?->last_tested_at?->format('M j, Y g:i A') ?? 'Never' }}
                    </div>
                </div>
            </div>

            @if ($integration?->last_error)
                <div class="mt-4 rounded-xl border border-danger-200 bg-danger-50 px-4 py-3 text-sm font-medium text-danger-700 dark:border-danger-500/30 dark:bg-danger-500/10 dark:text-danger-300">
                    {{ $integration->last_error }}
                </div>
            @endif
        </x-filament::section>

        <form wire:submit="saveAndActivate" class="space-y-6">
            {{ $this->form }}

            <div class="flex flex-wrap items-center gap-3">
                <x-filament::button
                    type="button"
                    color="gray"
                    wire:click="testConnection"
                    wire:loading.attr="disabled"
                    wire:target="testConnection,saveAndActivate,disableMpesa"
                >
                    <span wire:loading.remove wire:target="testConnection">Test Connection</span>
                    <span wire:loading wire:target="testConnection">Testing connection...</span>
                </x-filament::button>

                <x-filament::button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="saveAndActivate,testConnection,disableMpesa"
                >
                    <span wire:loading.remove wire:target="saveAndActivate">Save & Activate</span>
                    <span wire:loading wire:target="saveAndActivate">Saving...</span>
                </x-filament::button>

                @if ($integration?->is_active)
                    <x-filament::button
                        type="button"
                        color="danger"
                        wire:click="disableMpesa"
                        wire:loading.attr="disabled"
                        wire:target="disableMpesa,testConnection,saveAndActivate"
                    >
                        <span wire:loading.remove wire:target="disableMpesa">Disable M-Pesa</span>
                        <span wire:loading wire:target="disableMpesa">Disabling...</span>
                    </x-filament::button>
                @endif
            </div>
        </form>
    </div>
</x-filament-panels::page>
