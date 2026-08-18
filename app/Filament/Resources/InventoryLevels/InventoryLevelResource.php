<?php

namespace App\Filament\Resources\InventoryLevels;

use App\Filament\Resources\InventoryLevels\Pages\EditInventoryLevel;
use App\Filament\Resources\InventoryLevels\Pages\ListInventoryLevels;
use App\Filament\Resources\InventoryLevels\Schemas\InventoryLevelForm;
use App\Filament\Resources\InventoryLevels\Tables\InventoryLevelsTable;
use App\Models\InventoryLevel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InventoryLevelResource extends Resource
{
    protected static ?string $model = InventoryLevel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|\UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return InventoryLevelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventoryLevelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInventoryLevels::route('/'),
            'edit' => EditInventoryLevel::route('/{record}/edit'),
        ];
    }
}
