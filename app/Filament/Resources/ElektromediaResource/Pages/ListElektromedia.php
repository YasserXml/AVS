<?php

namespace App\Filament\Resources\ElektromediaResource\Pages;

use App\Filament\Resources\ElektromediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListElektromedia extends ListRecords
{
    protected static string $resource = ElektromediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
