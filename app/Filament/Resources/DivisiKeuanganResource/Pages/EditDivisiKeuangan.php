<?php

namespace App\Filament\Resources\DivisiKeuanganResource\Pages;

use App\Filament\Resources\DivisiKeuanganResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDivisiKeuangan extends EditRecord
{
    protected static string $resource = DivisiKeuanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
