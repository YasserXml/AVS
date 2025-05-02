<?php

namespace App\Filament\Resources\AsetptResource\Pages;

use App\Filament\Resources\AsetptResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAsetpt extends CreateRecord
{
    protected static string $resource = AsetptResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
