<?php

namespace App\Filament\Resources\HrdgamediaResource\Pages;

use App\Filament\Resources\HrdgamediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHrdgamedia extends EditRecord
{
    protected static string $resource = HrdgamediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
