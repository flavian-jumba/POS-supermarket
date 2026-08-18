<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product Information')
                    ->columns(2)
                    ->schema([
                        Select::make('organization_id')
                            ->relationship('organization', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->maxLength(255),
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('unit_id')
                            ->relationship('unit', 'name')
                            ->searchable()
                            ->preload(),
                        FileUpload::make('image_path')
                            ->image()
                            ->directory('products')
                            ->visibility('public'),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ]),
                Section::make('Pricing')
                    ->columns(2)
                    ->schema([
                        TextInput::make('cost_price')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('KSh'),
                        TextInput::make('selling_price')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('KSh'),
                    ]),
                Section::make('Inventory Settings')
                    ->columns(3)
                    ->schema([
                        Toggle::make('track_inventory')
                            ->required(),
                        TextInput::make('minimum_stock_level')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->required(),
                    ]),
            ]);
    }
}
