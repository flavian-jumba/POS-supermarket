<?php

namespace App\Filament\Widgets;

use App\Filament\Support\PosResourceUi;
use App\Models\InventoryLevel;
use App\Models\RegisterSession;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected ?string $heading = 'Store Performance';

    protected ?string $description = 'Live operating totals for the supermarket.';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $organization = Filament::getTenant();
        $branchIds = $organization->branches()->pluck('id');

        $todayRevenue = Sale::query()
            ->whereBelongsTo($organization)
            ->where('status', 'completed')
            ->whereDate('sold_at', today())
            ->sum('total');

        $todayTransactions = Sale::query()
            ->whereBelongsTo($organization)
            ->where('status', 'completed')
            ->whereDate('sold_at', today())
            ->count();

        $itemsSoldToday = SaleItem::query()
            ->whereHas('sale', function ($query): void {
                $query
                    ->whereBelongsTo(Filament::getTenant())
                    ->where('status', 'completed')
                    ->whereDate('sold_at', today());
            })
            ->sum('quantity');

        $lowStockProducts = InventoryLevel::query()
            ->whereIn('branch_id', $branchIds)
            ->where('quantity_on_hand', '>', 0)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->count();

        $openRegisters = RegisterSession::query()
            ->whereHas('register.branch', fn ($query) => $query->whereBelongsTo($organization))
            ->where('status', 'open')
            ->count();

        return [
            Stat::make("Today's Sales", PosResourceUi::money($todayRevenue))
                ->description($todayTransactions.' completed transactions')
                ->descriptionIcon(Heroicon::OutlinedShoppingCart)
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('success'),
            Stat::make('Items Sold Today', number_format((float) $itemsSoldToday, 3))
                ->description('Units moved through POS')
                ->descriptionIcon(Heroicon::OutlinedArchiveBox)
                ->icon(Heroicon::OutlinedShoppingCart)
                ->color('info'),
            Stat::make('Low Stock Lines', number_format($lowStockProducts))
                ->description('Inventory at or below reorder level')
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                ->icon(Heroicon::OutlinedArchiveBox)
                ->color($lowStockProducts > 0 ? 'warning' : 'success'),
            Stat::make('Open Registers', number_format($openRegisters))
                ->description('Cashier sessions currently active')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->icon(Heroicon::OutlinedCreditCard)
                ->color($openRegisters > 0 ? 'primary' : 'gray'),
        ];
    }
}
