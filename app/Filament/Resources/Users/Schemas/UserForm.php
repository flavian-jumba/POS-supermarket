<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('active'),
                Select::make('organization_role')
                    ->label('Organization Role')
                    ->options([
                        'admin' => 'Admin',
                        'manager' => 'Manager',
                        'cashier' => 'Cashier',
                    ])
                    ->default('cashier')
                    ->required()
                    ->dehydrated(false),
                Select::make('branch_ids')
                    ->label('Branches')
                    ->multiple()
                    ->relationship('branches', 'name', modifyQueryUsing: fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()))
                    ->searchable()
                    ->preload()
                    ->dehydrated(false),
            ]);
    }
}
