<?php

namespace App\Filament\Resources\BisnismediaResource\Pages;

use App\Filament\Resources\BisnismediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBisnismedia extends EditRecord
{
    protected static string $resource = BisnismediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
