<?php

namespace App\Filament\Resources\Divisi3dmediaResource\Pages;

use App\Filament\Resources\Divisi3dmediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDivisi3dmedia extends EditRecord
{
    protected static string $resource = Divisi3dmediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
