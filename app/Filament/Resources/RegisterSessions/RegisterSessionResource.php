<?php

namespace App\Filament\Resources\RegisterSessions;

use App\Filament\Resources\RegisterSessions\Pages\CreateRegisterSession;
use App\Filament\Resources\RegisterSessions\Pages\EditRegisterSession;
use App\Filament\Resources\RegisterSessions\Pages\ListRegisterSessions;
use App\Filament\Resources\RegisterSessions\Schemas\RegisterSessionForm;
use App\Filament\Resources\RegisterSessions\Tables\RegisterSessionsTable;
use App\Models\RegisterSession;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RegisterSessionResource extends Resource
{
    protected static ?string $model = RegisterSession::class;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales & POS';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return RegisterSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegisterSessionsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('register.branch', fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()));
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
            'index' => ListRegisterSessions::route('/'),
            'create' => CreateRegisterSession::route('/create'),
            'edit' => EditRegisterSession::route('/{record}/edit'),
        ];
    }
}
