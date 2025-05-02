<?php

namespace App\Filament\Resources\DivisimekanikResource\Pages;

use App\Filament\Resources\DivisimekanikResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDivisimekanik extends EditRecord
{
    protected static string $resource = DivisimekanikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
