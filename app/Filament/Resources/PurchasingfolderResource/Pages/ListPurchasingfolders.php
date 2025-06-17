<?php

namespace App\Filament\Resources\PurchasingfolderResource\Pages;

use App\Filament\Resources\PurchasingfolderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurchasingfolders extends ListRecords
{
    protected static string $resource = PurchasingfolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
