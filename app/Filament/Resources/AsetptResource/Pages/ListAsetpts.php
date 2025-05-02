<?php

namespace App\Filament\Resources\AsetptResource\Pages;

use App\Filament\Resources\AsetptResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAsetpts extends ListRecords
{
    protected static string $resource = AsetptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
