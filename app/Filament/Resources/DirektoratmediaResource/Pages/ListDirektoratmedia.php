<?php

namespace App\Filament\Resources\DirektoratmediaResource\Pages;

use App\Filament\Resources\DirektoratmediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDirektoratmedia extends ListRecords
{
    protected static string $resource = DirektoratmediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
