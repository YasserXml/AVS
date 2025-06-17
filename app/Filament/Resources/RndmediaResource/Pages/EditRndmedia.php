<?php

namespace App\Filament\Resources\RndmediaResource\Pages;

use App\Filament\Resources\RndmediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRndmedia extends EditRecord
{
    protected static string $resource = RndmediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
