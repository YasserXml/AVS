<?php

namespace App\Filament\Resources\PurchasingfolderResource\Pages;

use App\Filament\Resources\PurchasingfolderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchasingfolder extends EditRecord
{
    protected static string $resource = PurchasingfolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
