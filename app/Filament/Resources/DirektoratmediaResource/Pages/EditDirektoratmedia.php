<?php

namespace App\Filament\Resources\DirektoratmediaResource\Pages;

use App\Filament\Resources\DirektoratmediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDirektoratmedia extends EditRecord
{
    protected static string $resource = DirektoratmediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
