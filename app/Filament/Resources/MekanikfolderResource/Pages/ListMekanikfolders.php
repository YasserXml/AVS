<?php

namespace App\Filament\Resources\MekanikfolderResource\Pages;

use App\Filament\Resources\MekanikfolderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMekanikfolders extends ListRecords
{
    protected static string $resource = MekanikfolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
