<?php

namespace App\Filament\Resources\AsetptResource\Pages;

use App\Filament\Resources\AsetptResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAsetpt extends CreateRecord
{
    protected static string $resource = AsetptResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Aset berhasil ditambahkan')
            ->body('Data aset telah berhasil disimpan ke dalam sistem.');
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set tanggal ke hari ini jika tidak diisi
        if (empty($data['tanggal'])) {
            $data['tanggal'] = now()->toDateString();
        }
        
        return $data;
    }

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
