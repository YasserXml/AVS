<?php

namespace App\Filament\Resources\ManagerhrdfolderResource\Pages;

use App\Filament\Resources\ManagerhrdfolderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditManagerhrdfolder extends EditRecord
{
    protected static string $resource = ManagerhrdfolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
