<?php

namespace App\Filament\Resources\DivisipmoResource\Pages;

use App\Filament\Resources\DivisipmoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDivisipmos extends ListRecords
{
    protected static string $resource = DivisipmoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
