<?php

namespace App\Filament\Resources\SekretariatmediaResource\Pages;

use App\Filament\Resources\SekretariatmediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSekretariatmedia extends EditRecord
{
    protected static string $resource = SekretariatmediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
