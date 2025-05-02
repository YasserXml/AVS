<?php

namespace App\Filament\Resources\Divisi3dResource\Pages;

use App\Filament\Resources\Divisi3dResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDivisi3d extends EditRecord
{
    protected static string $resource = Divisi3dResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
