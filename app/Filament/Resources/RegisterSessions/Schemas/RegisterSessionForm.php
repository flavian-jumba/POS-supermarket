<?php

namespace App\Filament\Resources\RegisterSessions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RegisterSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Register Session')
                    ->columns(3)
                    ->schema([
                        Select::make('register_id')
                            ->relationship('register', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('status')
                            ->options([
                                'open' => 'Open',
                                'closed' => 'Closed',
                            ])
                            ->required()
                            ->default('open'),
                        TextInput::make('opening_float')
                            ->required()
                            ->numeric()
                            ->prefix('KSh')
                            ->default(0),
                        TextInput::make('expected_cash')
                            ->numeric()
                            ->prefix('KSh'),
                        TextInput::make('closing_cash')
                            ->numeric()
                            ->prefix('KSh'),
                        TextInput::make('cash_difference')
                            ->numeric()
                            ->prefix('KSh'),
                        DateTimePicker::make('opened_at')
                            ->required(),
                        DateTimePicker::make('closed_at'),
                    ]),
            ]);
    }
}
