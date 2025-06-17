<?php

namespace App\Filament\Resources\SoftwarefolderResource\Pages;

use App\Filament\Resources\SoftwarefolderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSoftwarefolders extends ListRecords
{
    protected static string $resource = SoftwarefolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
