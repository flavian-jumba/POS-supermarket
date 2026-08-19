<?php

namespace App\Filament\Resources\Units\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Unit')
                    ->columns(3)
                    ->schema([
                        Hidden::make('organization_id')
                            ->default(fn (): ?int => Filament::getTenant()?->id),
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('symbol')
                            ->required(),
                    ]),
            ]);
    }
}
