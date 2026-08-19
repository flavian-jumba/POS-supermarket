<?php

namespace App\Filament\Resources\Registers;

use App\Filament\Resources\Registers\Pages\CreateRegister;
use App\Filament\Resources\Registers\Pages\EditRegister;
use App\Filament\Resources\Registers\Pages\ListRegisters;
use App\Filament\Resources\Registers\Schemas\RegisterForm;
use App\Filament\Resources\Registers\Tables\RegistersTable;
use App\Models\Register;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RegisterResource extends Resource
{
    protected static ?string $model = Register::class;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales & POS';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return RegisterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegistersTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('branch', fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()));
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
            'index' => ListRegisters::route('/'),
            'create' => CreateRegister::route('/create'),
            'edit' => EditRegister::route('/{record}/edit'),
        ];
    }
}
