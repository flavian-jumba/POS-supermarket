<?php

namespace App\Filament\Support;

use App\Models\InventoryLevel;
use App\Models\Product;
use Filament\Support\Colors\Color;
use Illuminate\Support\Str;

class PosResourceUi
{
    public static function money(float|int|string|null $amount): string
    {
        return 'KSh '.number_format((float) $amount, 2);
    }

    public static function headline(?string $value): string
    {
        return Str::of($value ?? 'unknown')->replace('_', ' ')->headline()->toString();
    }

    public static function statusColor(?string $status): string|array|null
    {
        return match ($status) {
            'active', 'completed', 'successful', 'paid', 'open', 'in_stock' => 'success',
            'pending', 'draft', 'low_stock', 'unpaid', 'partial' => 'warning',
            'failed', 'cancelled', 'voided', 'inactive', 'out_of_stock' => 'danger',
            'closed' => 'gray',
            default => 'gray',
        };
    }

    public static function movementColor(?string $type): string|array|null
    {
        return match ($type) {
            'opening_stock', 'adjustment_in' => 'success',
            'sale', 'adjustment_out' => 'warning',
            default => 'gray',
        };
    }

    public static function stockStatus(InventoryLevel|Product $record): string
    {
        if ($record instanceof Product) {
            if (! $record->track_inventory) {
                return 'Available';
            }

            $quantity = (float) $record->inventoryLevels->sum('quantity_on_hand');
            $reorderLevel = (float) $record->minimum_stock_level;
        } else {
            $quantity = (float) $record->quantity_on_hand;
            $reorderLevel = (float) $record->reorder_level;
        }

        if ($quantity <= 0) {
            return 'Out of Stock';
        }

        if ($quantity <= $reorderLevel) {
            return 'Low Stock';
        }

        return 'In Stock';
    }

    public static function stockStatusColor(string $status): string|array|null
    {
        return match ($status) {
            'In Stock', 'Available' => 'success',
            'Low Stock' => 'warning',
            'Out of Stock' => 'danger',
            default => Color::Gray,
        };
    }
}
