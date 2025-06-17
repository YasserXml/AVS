<?php

namespace App\Filament\Resources\MekanikmediaResource\Pages;

use App\Filament\Resources\MekanikmediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMekanikmedia extends ListRecords
{
    protected static string $resource = MekanikmediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
