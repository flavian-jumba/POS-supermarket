<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Stock Movement')
                    ->columns(3)
                    ->schema([
                        Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->disabled(),
                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->disabled(),
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->disabled(),
                        TextInput::make('type')
                            ->disabled(),
                        TextInput::make('quantity')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('balance_after')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('unit_cost')
                            ->numeric()
                            ->prefix('KSh')
                            ->disabled(),
                        TextInput::make('reference_type')
                            ->disabled(),
                        TextInput::make('reference_id')
                            ->numeric()
                            ->disabled(),
                        Textarea::make('notes')
                            ->columnSpanFull()
                            ->disabled(),
                    ]),
            ]);
    }
}
