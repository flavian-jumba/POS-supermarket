<?php

namespace App\Filament\Resources\Sales\RelationManagers;

use App\Filament\Support\PosResourceUi;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->numeric(decimalPlaces: 3),
                TextColumn::make('unit_price')
                    ->formatStateUsing(fn ($state): string => PosResourceUi::money($state)),
                TextColumn::make('discount_amount')
                    ->label('Discount')
                    ->formatStateUsing(fn ($state): string => PosResourceUi::money($state)),
                TextColumn::make('tax_amount')
                    ->label('Tax')
                    ->formatStateUsing(fn ($state): string => PosResourceUi::money($state)),
                TextColumn::make('line_total')
                    ->formatStateUsing(fn ($state): string => PosResourceUi::money($state)),
            ]);
    }
}
