<?php

namespace App\Filament\Resources\SoftwaremediaResource\Pages;

use App\Filament\Resources\SoftwaremediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSoftwaremedia extends EditRecord
{
    protected static string $resource = SoftwaremediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
