<?php

namespace App\Filament\Resources\MekanikmediaResource\Pages;

use App\Filament\Resources\MekanikmediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMekanikmedia extends EditRecord
{
    protected static string $resource = MekanikmediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
