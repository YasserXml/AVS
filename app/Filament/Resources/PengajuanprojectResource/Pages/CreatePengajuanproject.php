<?php

namespace App\Filament\Resources\PengajuanprojectResource\Pages;

use App\Filament\Resources\PengajuanprojectResource;
use App\Models\Nameproject;
use App\Services\PengajuanEmailProjectService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class CreatePengajuanproject extends CreateRecord
{
    protected static string $resource = PengajuanprojectResource::class;

    public function getTitle(): string|Htmlable
    {
        return new HtmlString('
            <div class="flex items-center gap-2 ">
                <span class="text-xl font-bold">Buat Pengajuan Barang Project</span>
            </div>
        ');
    }

    protected function afterCreate(): void
    {
        // Kirim notifikasi ke admin
        PengajuanEmailProjectService::sendEmailToPm($this->record);
        
        // Notifikasi sukses untuk user yang mengajukan
        Notification::make()
            ->title('Pengajuan Berhasil Dikirim')
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->body('Pengajuan barang Anda telah berhasil dikirim dan akan diproses.')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Kirim Pengajuan')
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
