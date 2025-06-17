<?php

namespace App\Filament\Resources\PmofolderResource\Pages;

use App\Filament\Resources\PmofolderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPmofolders extends ListRecords
{
    protected static string $resource = PmofolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
