<?php

namespace App\Filament\Resources\RegisterSessions\Pages;

use App\Filament\Resources\RegisterSessions\RegisterSessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRegisterSessions extends ListRecords
{
    protected static string $resource = RegisterSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
