<?php

namespace App\Filament\Resources\PengajuanoprasionalResource\Pages;

use App\Filament\Resources\PengajuanoprasionalResource;
use App\Services\OprasionalNotificationService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class CreatePengajuanoprasional extends CreateRecord
{
    protected static string $resource = PengajuanoprasionalResource::class;

     protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        // Kirim notifikasi ke admin
        OprasionalNotificationService::sendNewPengajuanNotifications($this->record);
        
        // Notifikasi sukses untuk user yang mengajukan
        Notification::make()
            ->title('Pengajuan Berhasil Dikirim')
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->body('Pengajuan barang Anda telah berhasil dikirim dan akan diproses.')
            ->success()
            ->send();
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pengajuan barang berhasil dibuat';
    }

    public function getTitle(): string|Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-2 ">
                <span class="text-xl font-bold">Buat Pengajuan Barang Oprasional</span>
            </div>
        ');
    }
}
