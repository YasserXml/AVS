<?php

namespace App\Filament\Resources\DivisipmoResource\Pages;

use App\Filament\Resources\DivisipmoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDivisipmo extends EditRecord
{
    protected static string $resource = DivisipmoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
