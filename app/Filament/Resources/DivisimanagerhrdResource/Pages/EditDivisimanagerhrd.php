<?php

namespace App\Filament\Resources\DivisimanagerhrdResource\Pages;

use App\Filament\Resources\DivisimanagerhrdResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDivisimanagerhrd extends EditRecord
{
    protected static string $resource = DivisimanagerhrdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
