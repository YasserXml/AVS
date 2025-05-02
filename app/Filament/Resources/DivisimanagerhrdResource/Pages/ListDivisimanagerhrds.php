<?php

namespace App\Filament\Resources\DivisimanagerhrdResource\Pages;

use App\Filament\Resources\DivisimanagerhrdResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDivisimanagerhrds extends ListRecords
{
    protected static string $resource = DivisimanagerhrdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
