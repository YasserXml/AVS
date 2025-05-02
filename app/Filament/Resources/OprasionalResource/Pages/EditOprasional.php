<?php

namespace App\Filament\Resources\OprasionalResource\Pages;

use App\Filament\Resources\OprasionalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOprasional extends EditRecord
{
    protected static string $resource = OprasionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
