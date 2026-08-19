<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Setup Checklist
        </x-slot>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($this->getChecklist() as $item)
                <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-white/10 dark:bg-white/5">
                    <span @class([
                        'flex h-7 w-7 items-center justify-center rounded-full text-sm font-semibold',
                        'bg-primary-100 text-primary-700 dark:bg-primary-500/20 dark:text-primary-300' => $item['complete'],
                        'bg-gray-100 text-gray-400 dark:bg-white/10 dark:text-gray-500' => ! $item['complete'],
                    ])>
                        {{ $item['complete'] ? '✓' : '○' }}
                    </span>
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $item['label'] }}</span>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
