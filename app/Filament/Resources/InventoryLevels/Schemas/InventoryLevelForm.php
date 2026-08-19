<?php

namespace App\Filament\Resources\InventoryLevels\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

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
                            ->relationship('branch', 'name', modifyQueryUsing: fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(),
                        Select::make('product_id')
                            ->relationship('product', 'name', modifyQueryUsing: fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()))
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
