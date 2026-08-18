<?php

namespace App\Filament\Resources\InventoryLevels\Pages;

use App\Filament\Resources\InventoryLevels\InventoryLevelResource;
use Filament\Resources\Pages\ListRecords;

class ListInventoryLevels extends ListRecords
{
    protected static string $resource = InventoryLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
