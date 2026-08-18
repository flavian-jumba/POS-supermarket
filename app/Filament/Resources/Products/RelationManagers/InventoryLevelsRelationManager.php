<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Support\PosResourceUi;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InventoryLevelsRelationManager extends RelationManager
{
    protected static string $relationship = 'inventoryLevels';

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('branch.name')
            ->columns([
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable(),
                TextColumn::make('quantity_on_hand')
                    ->label('Quantity on Hand')
                    ->numeric(decimalPlaces: 3)
                    ->sortable(),
                TextColumn::make('reorder_level')
                    ->numeric(decimalPlaces: 3)
                    ->sortable(),
                TextColumn::make('stock_status')
                    ->label('Stock Status')
                    ->badge()
                    ->state(fn ($record): string => PosResourceUi::stockStatus($record))
                    ->color(fn (string $state): string|array|null => PosResourceUi::stockStatusColor($state)),
            ]);
    }
}
