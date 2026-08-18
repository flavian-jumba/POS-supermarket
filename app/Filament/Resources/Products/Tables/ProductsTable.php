<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Support\PosResourceUi;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->square(),
                TextColumn::make('name')
                    ->label('Product')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->searchable(),
                TextColumn::make('selling_price')
                    ->formatStateUsing(fn ($state): string => PosResourceUi::money($state))
                    ->sortable(),
                TextColumn::make('current_stock')
                    ->label('Current Stock')
                    ->state(fn ($record): string => number_format((float) $record->inventoryLevels->sum('quantity_on_hand'), 3))
                    ->sortable(false),
                IconColumn::make('track_inventory')
                    ->label('Tracks Inventory')
                    ->boolean(),
                TextColumn::make('stock_status')
                    ->label('Availability')
                    ->badge()
                    ->state(fn ($record): string => PosResourceUi::stockStatus($record))
                    ->color(fn (string $state): string|array|null => PosResourceUi::stockStatusColor($state)),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
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
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Active'),
                TernaryFilter::make('track_inventory')
                    ->label('Tracks Inventory'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
