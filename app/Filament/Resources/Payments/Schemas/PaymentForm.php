<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment')
                    ->columns(2)
                    ->schema([
                        Select::make('sale_id')
                            ->relationship('sale', 'sale_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('register_session_id')
                            ->relationship('registerSession', 'id')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('method')
                            ->options([
                                'cash' => 'Cash',
                                'mpesa' => 'M-Pesa',
                                'card' => 'Card',
                            ])
                            ->required(),
                        TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('KSh'),
                        TextInput::make('reference'),
                        Select::make('status')
                            ->options([
                                'successful' => 'Successful',
                                'completed' => 'Completed',
                                'pending' => 'Pending',
                                'failed' => 'Failed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('completed'),
                        DateTimePicker::make('paid_at')
                            ->required(),
                    ]),
            ]);
    }
}
