<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category')
                    ->columns(2)
                    ->schema([
                        Hidden::make('organization_id')
                            ->default(fn (): ?int => Filament::getTenant()?->id),
                        TextInput::make('name')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (?string $state, Set $set) => $set('slug', Str::slug($state ?? '')))
                            ->required(),
                        TextInput::make('slug')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->required(),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
