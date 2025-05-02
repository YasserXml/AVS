<?php

namespace App\Filament\Resources\DivisiElektroResource\Pages;

use App\Filament\Resources\DivisiElektroResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDivisiElektro extends EditRecord
{
    protected static string $resource = DivisiElektroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
