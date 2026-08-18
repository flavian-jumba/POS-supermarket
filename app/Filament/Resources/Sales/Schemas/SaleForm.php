<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sale Details')
                    ->columns(3)
                    ->schema([
                        TextInput::make('sale_number')
                            ->required()
                            ->disabled(),
                        Select::make('organization_id')
                            ->relationship('organization', 'name')
                            ->disabled(),
                        Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->disabled(),
                        Select::make('register_id')
                            ->relationship('register', 'name')
                            ->disabled(),
                        Select::make('register_session_id')
                            ->relationship('registerSession', 'id')
                            ->disabled(),
                        Select::make('cashier_id')
                            ->relationship('cashier', 'name')
                            ->disabled(),
                        DateTimePicker::make('sold_at')
                            ->required()
                            ->disabled(),
                    ]),
                Section::make('Totals')
                    ->columns(3)
                    ->schema([
                        TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('KSh')
                            ->disabled(),
                        TextInput::make('discount_total')
                            ->numeric()
                            ->prefix('KSh')
                            ->disabled(),
                        TextInput::make('tax_total')
                            ->numeric()
                            ->prefix('KSh')
                            ->disabled(),
                        TextInput::make('total')
                            ->numeric()
                            ->prefix('KSh')
                            ->disabled(),
                        TextInput::make('status')
                            ->disabled(),
                        TextInput::make('payment_status')
                            ->disabled(),
                    ]),
            ]);
    }
}
