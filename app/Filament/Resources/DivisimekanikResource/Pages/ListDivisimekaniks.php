<?php

namespace App\Filament\Resources\DivisimekanikResource\Pages;

use App\Filament\Resources\DivisimekanikResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDivisimekaniks extends ListRecords
{
    protected static string $resource = DivisimekanikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
