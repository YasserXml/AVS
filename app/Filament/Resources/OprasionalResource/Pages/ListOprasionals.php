<?php

namespace App\Filament\Resources\OprasionalResource\Pages;

use App\Filament\Resources\OprasionalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOprasionals extends ListRecords
{
    protected static string $resource = OprasionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
