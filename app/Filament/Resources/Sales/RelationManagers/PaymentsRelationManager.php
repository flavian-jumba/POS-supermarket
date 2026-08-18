<?php

namespace App\Filament\Resources\Sales\RelationManagers;

use App\Filament\Support\PosResourceUi;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference')
            ->columns([
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
            ]);
    }
}
