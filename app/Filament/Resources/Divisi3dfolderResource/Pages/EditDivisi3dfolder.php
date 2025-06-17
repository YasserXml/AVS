<?php

namespace App\Filament\Resources\Divisi3dfolderResource\Pages;

use App\Filament\Resources\Divisi3dfolderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDivisi3dfolder extends EditRecord
{
    protected static string $resource = Divisi3dfolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
