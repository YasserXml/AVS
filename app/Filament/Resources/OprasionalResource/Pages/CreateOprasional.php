<?php

namespace App\Filament\Resources\OprasionalResource\Pages;

use App\Filament\Resources\OprasionalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOprasional extends CreateRecord
{
    protected static string $resource = OprasionalResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
