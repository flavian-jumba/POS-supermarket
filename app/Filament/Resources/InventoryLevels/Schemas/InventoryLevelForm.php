<?php

namespace App\Filament\Resources\InventoryLevels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InventoryLevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Current Stock')
                    ->columns(2)
                    ->schema([
                        Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(),
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(),
                        TextInput::make('quantity_on_hand')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                        TextInput::make('reorder_level')
                            ->required()
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
