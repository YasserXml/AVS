<?php

namespace App\Filament\Resources\MekanikfolderResource\Pages;

use App\Filament\Resources\MekanikfolderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMekanikfolder extends EditRecord
{
    protected static string $resource = MekanikfolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
