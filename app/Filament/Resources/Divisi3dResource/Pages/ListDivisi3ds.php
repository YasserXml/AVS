<?php

namespace App\Filament\Resources\Divisi3dResource\Pages;

use App\Filament\Resources\Divisi3dResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDivisi3ds extends ListRecords
{
    protected static string $resource = Divisi3dResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
