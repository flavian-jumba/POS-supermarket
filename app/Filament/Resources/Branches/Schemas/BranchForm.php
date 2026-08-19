<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Branch')
                    ->columns(2)
                    ->schema([
                        Hidden::make('organization_id')
                            ->default(fn (): ?int => Filament::getTenant()?->id),
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('code')
                            ->required(),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->required()
                            ->default('active'),
                        TextInput::make('phone')
                            ->tel(),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email(),
                        Textarea::make('address')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
