<?php

namespace App\Filament\Resources\PurchasingmediaResource\Pages;

use App\Filament\Resources\PurchasingmediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchasingmedia extends EditRecord
{
    protected static string $resource = PurchasingmediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
