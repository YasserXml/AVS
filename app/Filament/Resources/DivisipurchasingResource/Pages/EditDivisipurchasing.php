<?php

namespace App\Filament\Resources\DivisipurchasingResource\Pages;

use App\Filament\Resources\DivisipurchasingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDivisipurchasing extends EditRecord
{
    protected static string $resource = DivisipurchasingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
