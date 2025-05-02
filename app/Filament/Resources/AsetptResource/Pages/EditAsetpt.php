<?php

namespace App\Filament\Resources\AsetptResource\Pages;

use App\Filament\Resources\AsetptResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAsetpt extends EditRecord
{
    protected static string $resource = AsetptResource::class;

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
