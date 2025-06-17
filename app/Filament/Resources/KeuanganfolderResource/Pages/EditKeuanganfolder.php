<?php

namespace App\Filament\Resources\KeuanganfolderResource\Pages;

use App\Filament\Resources\KeuanganfolderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKeuanganfolder extends EditRecord
{
    protected static string $resource = KeuanganfolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
