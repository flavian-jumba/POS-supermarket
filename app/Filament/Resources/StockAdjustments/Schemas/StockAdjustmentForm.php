<?php

namespace App\Filament\Resources\StockAdjustments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockAdjustmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Stock Adjustment')
                    ->columns(2)
                    ->schema([
                        Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('created_by')
                            ->relationship('creator', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('adjustment_number')
                            ->required(),
                        Select::make('type')
                            ->options([
                                'adjustment_in' => 'Adjustment In',
                                'adjustment_out' => 'Adjustment Out',
                            ])
                            ->required(),
                        TextInput::make('reason'),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('draft'),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
