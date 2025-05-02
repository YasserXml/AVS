<?php

namespace App\Filament\Resources\DivisihrdgaResource\Pages;

use App\Filament\Resources\DivisihrdgaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDivisihrdgas extends ListRecords
{
    protected static string $resource = DivisihrdgaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
