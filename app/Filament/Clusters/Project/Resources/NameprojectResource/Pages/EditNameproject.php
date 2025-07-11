<?php

namespace App\Filament\Clusters\Project\Resources\NameprojectResource\Pages;

use App\Filament\Clusters\Project\Resources\NameprojectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNameproject extends EditRecord
{
    protected static string $resource = NameprojectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
