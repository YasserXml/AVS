<?php

namespace App\Filament\Resources\SoftwaremediaResource\Pages;

use App\Filament\Resources\SoftwaremediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSoftwaremedia extends ListRecords
{
    protected static string $resource = SoftwaremediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
