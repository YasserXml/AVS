<?php

namespace App\Filament\Resources\PengajuanprojectResource\Pages;

use App\Filament\Resources\PengajuanprojectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanproject extends EditRecord
{
    protected static string $resource = PengajuanprojectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
