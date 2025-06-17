<?php

namespace App\Filament\Resources\KeuanganmediaResource\Pages;

use App\Filament\Resources\KeuanganmediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKeuanganmedia extends EditRecord
{
    protected static string $resource = KeuanganmediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
