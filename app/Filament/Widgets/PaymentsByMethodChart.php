<?php

namespace App\Filament\Widgets;

use App\Filament\Support\PosResourceUi;
use App\Models\Payment;
use Filament\Widgets\ChartWidget;

class PaymentsByMethodChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Payment Mix';

    protected ?string $description = 'Revenue split by payment method.';

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $totalsByMethod = Payment::query()
            ->selectRaw('method, SUM(amount) as revenue')
            ->where('status', 'completed')
            ->groupBy('method')
            ->orderBy('method')
            ->pluck('revenue', 'method');

        return [
            'datasets' => [
                [
                    'label' => 'Payments',
                    'data' => $totalsByMethod
                        ->map(fn (string $revenue): float => (float) $revenue)
                        ->values()
                        ->all(),
                    'backgroundColor' => [
                        '#22c55e',
                        '#f59e0b',
                        '#3b82f6',
                        '#ef4444',
                        '#8b5cf6',
                    ],
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => $totalsByMethod
                ->keys()
                ->map(fn (string $method): string => PosResourceUi::headline($method))
                ->values()
                ->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
