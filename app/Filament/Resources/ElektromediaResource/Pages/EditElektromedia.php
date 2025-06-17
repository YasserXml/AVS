<?php

namespace App\Filament\Resources\ElektromediaResource\Pages;

use App\Filament\Resources\ElektromediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditElektromedia extends EditRecord
{
    protected static string $resource = ElektromediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
