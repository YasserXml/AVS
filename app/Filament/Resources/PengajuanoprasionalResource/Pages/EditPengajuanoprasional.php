<?php

namespace App\Filament\Resources\PengajuanoprasionalResource\Pages;

use App\Filament\Resources\PengajuanoprasionalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanoprasional extends EditRecord
{
    protected static string $resource = PengajuanoprasionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
