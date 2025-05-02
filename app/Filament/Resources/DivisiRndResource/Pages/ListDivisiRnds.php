<?php

namespace App\Filament\Resources\DivisiRndResource\Pages;

use App\Filament\Resources\DivisiRndResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDivisiRnds extends ListRecords
{
    protected static string $resource = DivisiRndResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
