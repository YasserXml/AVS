<?php

namespace App\Filament\Resources\PmomediaResource\Pages;

use App\Filament\Resources\PmomediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPmomedia extends EditRecord
{
    protected static string $resource = PmomediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
