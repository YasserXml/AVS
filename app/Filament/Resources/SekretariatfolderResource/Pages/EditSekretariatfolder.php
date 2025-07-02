<?php

namespace App\Filament\Resources\SekretariatfolderResource\Pages;

use App\Filament\Resources\SekretariatfolderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSekretariatfolder extends EditRecord
{
    protected static string $resource = SekretariatfolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
