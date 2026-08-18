<?php

namespace App\Filament\Widgets;

use App\Models\InventoryLevel;
use Filament\Widgets\ChartWidget;

class InventoryStatusChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Inventory Health';

    protected ?string $description = 'Stock lines grouped by replenishment status.';

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
        $outOfStock = InventoryLevel::query()
            ->where('quantity_on_hand', '<=', 0)
            ->count();

        $lowStock = InventoryLevel::query()
            ->where('quantity_on_hand', '>', 0)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->count();

        $inStock = InventoryLevel::query()
            ->whereColumn('quantity_on_hand', '>', 'reorder_level')
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Stock lines',
                    'data' => [$inStock, $lowStock, $outOfStock],
                    'backgroundColor' => [
                        '#22c55e',
                        '#f59e0b',
                        '#ef4444',
                    ],
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => ['In Stock', 'Low Stock', 'Out of Stock'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
