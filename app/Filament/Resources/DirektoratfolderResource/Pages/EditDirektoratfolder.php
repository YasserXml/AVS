<?php

namespace App\Filament\Resources\DirektoratfolderResource\Pages;

use App\Filament\Resources\DirektoratfolderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDirektoratfolder extends EditRecord
{
    protected static string $resource = DirektoratfolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat'),
            Actions\DeleteAction::make()
                ->label('Hapus'),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Jika password kosong, jangan update
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            // Hash password jika ada
            $data['password'] = bcrypt($data['password']);
        }
        
        return $data;
    }
}
