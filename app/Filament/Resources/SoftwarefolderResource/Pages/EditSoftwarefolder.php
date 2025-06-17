<?php

namespace App\Filament\Resources\SoftwarefolderResource\Pages;

use App\Filament\Resources\SoftwarefolderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSoftwarefolder extends EditRecord
{
    protected static string $resource = SoftwarefolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
