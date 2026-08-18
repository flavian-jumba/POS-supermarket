<?php

namespace App\Filament\Resources\InventoryLevels\Pages;

use App\Filament\Resources\InventoryLevels\InventoryLevelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInventoryLevel extends EditRecord
{
    protected static string $resource = InventoryLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
