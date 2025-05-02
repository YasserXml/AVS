<?php

namespace App\Filament\Resources\DivisiRndResource\Pages;

use App\Filament\Resources\DivisiRndResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDivisiRnd extends EditRecord
{
    protected static string $resource = DivisiRndResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
