<?php

namespace App\Filament\Resources\HrdgamediaResource\Pages;

use App\Filament\Resources\HrdgamediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHrdgamedia extends ListRecords
{
    protected static string $resource = HrdgamediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
