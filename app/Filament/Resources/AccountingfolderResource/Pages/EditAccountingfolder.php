<?php

namespace App\Filament\Resources\AccountingfolderResource\Pages;

use App\Filament\Resources\AccountingfolderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountingfolder extends EditRecord
{
    protected static string $resource = AccountingfolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
