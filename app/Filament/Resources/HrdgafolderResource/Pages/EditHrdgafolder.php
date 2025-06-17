<?php

namespace App\Filament\Resources\HrdgafolderResource\Pages;

use App\Filament\Resources\HrdgafolderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHrdgafolder extends EditRecord
{
    protected static string $resource = HrdgafolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
