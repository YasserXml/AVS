<?php

namespace App\Filament\Resources\KeuanganmediaResource\Pages;

use App\Filament\Resources\KeuanganmediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKeuanganmedia extends ListRecords
{
    protected static string $resource = KeuanganmediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
