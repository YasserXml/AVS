<?php

namespace App\Filament\Resources\Divisi3dfolderResource\Pages;

use App\Filament\Resources\Divisi3dfolderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDivisi3dfolders extends ListRecords
{
    protected static string $resource = Divisi3dfolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
