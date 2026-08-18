<?php

namespace App\Filament\Resources\InventoryLevels\Tables;

use App\Filament\Support\PosResourceUi;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryLevelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable(),
                TextColumn::make('quantity_on_hand')
                    ->label('Quantity on Hand')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reorder_level')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock_status')
                    ->label('Status')
                    ->badge()
                    ->state(fn ($record): string => PosResourceUi::stockStatus($record))
                    ->color(fn (string $state): string|array|null => PosResourceUi::stockStatusColor($state)),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('branch')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('low_stock')
                    ->label('Low Stock')
                    ->queries(
                        true: fn (Builder $query) => $query->whereColumn('quantity_on_hand', '<=', 'reorder_level')->where('quantity_on_hand', '>', 0),
                        false: fn (Builder $query) => $query->whereColumn('quantity_on_hand', '>', 'reorder_level'),
                    ),
                TernaryFilter::make('out_of_stock')
                    ->label('Out of Stock')
                    ->queries(
                        true: fn (Builder $query) => $query->where('quantity_on_hand', '<=', 0),
                        false: fn (Builder $query) => $query->where('quantity_on_hand', '>', 0),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
