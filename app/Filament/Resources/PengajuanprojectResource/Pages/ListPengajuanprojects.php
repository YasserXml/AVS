<?php

namespace App\Filament\Resources\PengajuanprojectResource\Pages;

use App\Filament\Resources\PengajuanprojectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanprojects extends ListRecords
{
    protected static string $resource = PengajuanprojectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
