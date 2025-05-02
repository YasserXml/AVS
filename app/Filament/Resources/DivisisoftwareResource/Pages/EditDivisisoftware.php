<?php

namespace App\Filament\Resources\DivisisoftwareResource\Pages;

use App\Filament\Resources\DivisisoftwareResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDivisisoftware extends EditRecord
{
    protected static string $resource = DivisisoftwareResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
