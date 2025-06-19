<?php

namespace App\Filament\Resources\ManagerhrdfolderResource\Pages;

use App\Filament\Resources\ManagerhrdfolderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListManagerhrdfolders extends ListRecords
{
    protected static string $resource = ManagerhrdfolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Folder'),
        ];
    }
}
