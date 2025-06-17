<?php

namespace App\Filament\Resources\HrdgafolderResource\Pages;

use App\Filament\Resources\HrdgafolderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHrdgafolders extends ListRecords
{
    protected static string $resource = HrdgafolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
