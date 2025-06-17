<?php

namespace App\Filament\Resources\ManagerhrdmediaResource\Pages;

use App\Filament\Resources\ManagerhrdmediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListManagerhrdmedia extends ListRecords
{
    protected static string $resource = ManagerhrdmediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
