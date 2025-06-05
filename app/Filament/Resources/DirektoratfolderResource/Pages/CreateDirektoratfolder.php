<?php

namespace App\Filament\Resources\DirektoratfolderResource\Pages;

use App\Filament\Resources\DirektoratfolderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDirektoratfolder extends CreateRecord
{
    protected static string $resource = DirektoratfolderResource::class;

     protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Simpan Data')
                ->icon('heroicon-o-check')
                ->size('lg')
                ->extraAttributes([
                    'class' => 'font-semibold'
                ]),
            $this->getCancelFormAction()
                ->label('Batal')
                ->icon('heroicon-o-x-mark')
                ->color('gray'),
        ];
    }
}
