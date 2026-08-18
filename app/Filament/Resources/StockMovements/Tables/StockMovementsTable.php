<?php

namespace App\Filament\Resources\StockMovements\Tables;

use App\Filament\Support\PosResourceUi;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => PosResourceUi::headline($state))
                    ->color(fn (?string $state): string|array|null => PosResourceUi::movementColor($state)),
                TextColumn::make('quantity')
                    ->numeric(decimalPlaces: 3)
                    ->sortable(),
                TextColumn::make('balance_after')
                    ->numeric(decimalPlaces: 3)
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('unit_cost')
                    ->formatStateUsing(fn ($state): string => PosResourceUi::money($state))
                    ->sortable(),
                TextColumn::make('reference')
                    ->state(fn ($record): string => $record->reference_type ? class_basename($record->reference_type).' #'.$record->reference_id : '-'),
                TextColumn::make('notes')
                    ->limit(40),
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
                SelectFilter::make('type')
                    ->options([
                        'opening_stock' => 'Opening Stock',
                        'sale' => 'Sale',
                        'adjustment_in' => 'Adjustment In',
                        'adjustment_out' => 'Adjustment Out',
                    ]),
            ]);
    }
}
