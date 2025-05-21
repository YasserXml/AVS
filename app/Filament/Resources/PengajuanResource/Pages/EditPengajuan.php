<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use App\Models\Barang;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditPengajuan extends EditRecord
{
    protected static string $resource = PengajuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->status === 'pending'),
                
            Actions\Action::make('back')
                ->label('Kembali')
                ->color('gray')
                ->url(fn() => $this->getResource()::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Hanya bisa edit pengajuan dengan status pending
        if ($this->record->status !== 'pending') {
            Notification::make()
                ->title('Pengajuan Tidak Dapat Diubah')
                ->body('Pengajuan yang sudah diproses tidak dapat diubah.')
                ->danger()
                ->persistent()
                ->send();
                
            $this->halt();
        }
        
        // Verifikasi stok terakhir jika ada perubahan jumlah
        if ($data['Jumlah_barang_diajukan'] != $this->record->Jumlah_barang_diajukan) {
            $barang = Barang::find($this->record->barang_id);
            
            if ($barang->jumlah_barang < $data['Jumlah_barang_diajukan']) {
                Notification::make()
                    ->title('Stok Tidak Mencukupi')
                    ->body("Stok barang \"{$barang->nama_barang}\" hanya tersedia {$barang->jumlah_barang} unit saat ini.")
                    ->danger()
                    ->persistent()
                    ->send();
                    
                $this->halt();
            }
        }
        
        // Pastikan status tetap pending saat edit
        $data['status'] = 'pending';
        
        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Pengajuan barang berhasil diperbarui';
    }
}