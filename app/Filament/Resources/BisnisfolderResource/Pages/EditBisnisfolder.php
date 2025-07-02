<?php

namespace App\Filament\Resources\BisnisfolderResource\Pages;

use App\Filament\Resources\BisnisfolderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBisnisfolder extends EditRecord
{
    protected static string $resource = BisnisfolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
