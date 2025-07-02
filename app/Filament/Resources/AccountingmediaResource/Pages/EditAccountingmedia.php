<?php

namespace App\Filament\Resources\AccountingmediaResource\Pages;

use App\Filament\Resources\AccountingmediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountingmedia extends EditRecord
{
    protected static string $resource = AccountingmediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
