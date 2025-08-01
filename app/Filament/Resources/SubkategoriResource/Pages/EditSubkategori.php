<?php

namespace App\Filament\Resources\SubkategoriResource\Pages;

use App\Filament\Resources\SubkategoriResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubkategori extends EditRecord
{
    protected static string $resource = SubkategoriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
