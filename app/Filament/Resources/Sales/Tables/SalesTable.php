<?php

namespace App\Filament\Resources\Sales\Tables;

use App\Filament\Support\PosResourceUi;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sale_number')
                    ->label('Sale Number')
                    ->searchable(),
                TextColumn::make('sold_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable(),
                TextColumn::make('register.name')
                    ->label('Register')
                    ->searchable(),
                TextColumn::make('cashier.name')
                    ->label('Cashier')
                    ->searchable(),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->sortable(),
                TextColumn::make('total')
                    ->formatStateUsing(fn ($state): string => PosResourceUi::money($state))
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => PosResourceUi::headline($state))
                    ->color(fn (?string $state): string|array|null => PosResourceUi::statusColor($state)),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => PosResourceUi::headline($state))
                    ->color(fn (?string $state): string|array|null => PosResourceUi::statusColor($state)),
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
                SelectFilter::make('register')
                    ->relationship('register', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('payment_status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'completed' => 'Completed',
                        'voided' => 'Voided',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
