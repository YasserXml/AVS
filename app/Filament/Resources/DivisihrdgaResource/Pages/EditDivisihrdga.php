<?php

namespace App\Filament\Resources\DivisihrdgaResource\Pages;

use App\Filament\Resources\DivisihrdgaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDivisihrdga extends EditRecord
{
    protected static string $resource = DivisihrdgaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
