<?php

namespace App\Filament\Resources\DivisisoftwareResource\Pages;

use App\Filament\Resources\DivisisoftwareResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDivisisoftware extends ListRecords
{
    protected static string $resource = DivisisoftwareResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
