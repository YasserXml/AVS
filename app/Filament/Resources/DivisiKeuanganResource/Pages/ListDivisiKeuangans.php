<?php

namespace App\Filament\Resources\DivisiKeuanganResource\Pages;

use App\Filament\Resources\DivisiKeuanganResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDivisiKeuangans extends ListRecords
{
    protected static string $resource = DivisiKeuanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
