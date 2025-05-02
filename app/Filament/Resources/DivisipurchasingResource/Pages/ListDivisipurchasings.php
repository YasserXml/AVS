<?php

namespace App\Filament\Resources\DivisipurchasingResource\Pages;

use App\Filament\Resources\DivisipurchasingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDivisipurchasings extends ListRecords
{
    protected static string $resource = DivisipurchasingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
