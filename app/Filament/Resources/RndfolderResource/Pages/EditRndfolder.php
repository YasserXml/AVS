<?php

namespace App\Filament\Resources\RndfolderResource\Pages;

use App\Filament\Resources\RndfolderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRndfolder extends EditRecord
{
    protected static string $resource = RndfolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
