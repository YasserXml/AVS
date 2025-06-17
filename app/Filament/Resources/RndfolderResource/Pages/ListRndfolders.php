<?php

namespace App\Filament\Resources\RndfolderResource\Pages;

use App\Filament\Resources\RndfolderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRndfolders extends ListRecords
{
    protected static string $resource = RndfolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
