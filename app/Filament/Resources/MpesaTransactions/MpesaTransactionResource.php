<?php

namespace App\Filament\Resources\MpesaTransactions;

use App\Filament\Resources\MpesaTransactions\Pages\ListMpesaTransactions;
use App\Filament\Resources\MpesaTransactions\Tables\MpesaTransactionsTable;
use App\Models\MpesaTransaction;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MpesaTransactionResource extends Resource
{
    protected static ?string $model = MpesaTransaction::class;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales & POS';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return MpesaTransactionsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereBelongsTo(Filament::getTenant())
            ->with(['sale', 'payment']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMpesaTransactions::route('/'),
        ];
    }
}
