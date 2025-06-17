<?php

namespace App\Filament\Resources\PmofolderResource\Pages;

use App\Filament\Resources\PmofolderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPmofolder extends EditRecord
{
    protected static string $resource = PmofolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
