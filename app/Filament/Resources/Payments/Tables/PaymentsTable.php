<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Filament\Support\PosResourceUi;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sale.sale_number')
                    ->label('Sale')
                    ->searchable(),
                TextColumn::make('method')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => PosResourceUi::headline($state)),
                TextColumn::make('amount')
                    ->formatStateUsing(fn ($state): string => PosResourceUi::money($state))
                    ->sortable(),
                TextColumn::make('reference')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => PosResourceUi::headline($state))
                    ->color(fn (?string $state): string|array|null => PosResourceUi::statusColor($state)),
                TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable(),
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
                SelectFilter::make('method')
                    ->options([
                        'cash' => 'Cash',
                        'mpesa' => 'M-Pesa',
                        'card' => 'Card',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'successful' => 'Successful',
                        'completed' => 'Completed',
                        'pending' => 'Pending',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
