<?php

namespace App\Filament\Resources\ElektrofolderResource\Pages;

use App\Filament\Resources\ElektrofolderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditElektrofolder extends EditRecord
{
    protected static string $resource = ElektrofolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
