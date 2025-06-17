<?php

namespace App\Filament\Resources\RndmediaResource\Pages;

use App\Filament\Resources\RndmediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRndmedia extends ListRecords
{
    protected static string $resource = RndmediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
