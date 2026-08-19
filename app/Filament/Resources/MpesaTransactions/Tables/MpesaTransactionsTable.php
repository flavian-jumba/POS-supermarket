<?php

namespace App\Filament\Resources\MpesaTransactions\Tables;

use App\Filament\Support\PosResourceUi;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MpesaTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('requested_at', 'desc')
            ->columns([
                TextColumn::make('requested_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('sale.sale_number')
                    ->label('Sale')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('amount')
                    ->formatStateUsing(fn ($state): string => PosResourceUi::money($state))
                    ->sortable(),
                TextColumn::make('mpesa_receipt_number')
                    ->label('Receipt')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => PosResourceUi::headline($state))
                    ->color(fn (?string $state): string|array|null => PosResourceUi::statusColor($state)),
                TextColumn::make('result_description')
                    ->label('Result')
                    ->limit(48),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'successful' => 'Successful',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                        'timeout' => 'Timeout',
                    ]),
                Filter::make('requested_at')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('requested_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date) => $query->whereDate('requested_at', '<=', $date))),
                SelectFilter::make('environment')
                    ->options([
                        'sandbox' => 'Sandbox',
                        'production' => 'Production',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, string $environment) => $query->whereHas('organization.mpesaIntegration', fn (Builder $query) => $query->where('environment', $environment)),
                    )),
            ]);
    }
}
