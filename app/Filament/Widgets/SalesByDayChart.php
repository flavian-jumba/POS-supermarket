<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;

class SalesByDayChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Sales by Day';

    protected ?string $description = 'Completed sales revenue over the last 7 days.';

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $startDate = CarbonImmutable::today()->subDays(6);
        $endDate = CarbonImmutable::today();

        $salesByDate = Sale::query()
            ->selectRaw('DATE(sold_at) as sale_date, SUM(total) as revenue')
            ->where('status', 'completed')
            ->whereBetween('sold_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->groupByRaw('DATE(sold_at)')
            ->pluck('revenue', 'sale_date');

        $labels = [];
        $sales = [];

        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            $dateKey = $date->toDateString();

            $labels[] = $date->format('M j');
            $sales[] = (float) ($salesByDate[$dateKey] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data' => $sales,
                    'backgroundColor' => '#22c55e',
                    'borderColor' => '#16a34a',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
