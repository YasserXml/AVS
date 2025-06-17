<?php

namespace App\Filament\Resources\ManagerhrdmediaResource\Pages;

use App\Filament\Resources\ManagerhrdmediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditManagerhrdmedia extends EditRecord
{
    protected static string $resource = ManagerhrdmediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
