<?php

namespace App\Filament\Resources\RegisterSessions\Pages;

use App\Filament\Resources\RegisterSessions\RegisterSessionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRegisterSession extends EditRecord
{
    protected static string $resource = RegisterSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
