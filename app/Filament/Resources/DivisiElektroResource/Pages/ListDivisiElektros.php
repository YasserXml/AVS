<?php

namespace App\Filament\Resources\DivisiElektroResource\Pages;

use App\Filament\Resources\DivisiElektroResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDivisiElektros extends ListRecords
{
    protected static string $resource = DivisiElektroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
