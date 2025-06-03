<?php

namespace App\Filament\Resources\AsetptResource\Pages;

use App\Filament\Resources\AsetptResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAsetpt extends EditRecord
{
    protected static string $resource = AsetptResource::class;

     protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat'),
            Actions\DeleteAction::make()
                ->label('Hapus'),
            Actions\RestoreAction::make()
                ->label('Pulihkan'),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Aset berhasil diperbarui')
            ->body('Perubahan data aset telah berhasil disimpan.');
    }
}
