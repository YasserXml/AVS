<?php

namespace App\Filament\Resources\KeuanganfolderResource\Pages;

use App\Filament\Resources\KeuanganfolderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKeuanganfolders extends ListRecords
{
    protected static string $resource = KeuanganfolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
