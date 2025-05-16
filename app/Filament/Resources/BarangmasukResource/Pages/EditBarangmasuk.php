<?php

namespace App\Filament\Resources\BarangmasukResource\Pages;

use App\Filament\Resources\BarangmasukResource;
use App\Models\Barang;
use App\Models\Barangmasuk;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditBarangmasuk extends EditRecord
{
    protected static string $resource = BarangmasukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (Barangmasuk $record, Actions\DeleteAction $action) {
                    // Ambil data barang sebelum dihapus untuk mengembalikan stok
                    $barang = Barang::findOrFail($record->barang_id);
                    $jumlahBarangMasuk = $record->jumlah_barang_masuk;
                    
                    // Kembalikan stok barang saat data barang masuk dihapus
                    DB::transaction(function () use ($barang, $jumlahBarangMasuk, $action) {
                        $newStock = $barang->jumlah_barang - $jumlahBarangMasuk;
                        
                        // Pastikan stok tidak menjadi negatif
                        if ($newStock >= 0) {
                            $barang->update(['jumlah_barang' => $newStock]);
                        } else {
                            // Jika stok jadi negatif, batalkan penghapusan
                            $action->cancel();
                            Notification::make()
                                ->danger()
                                ->title('Gagal menghapus data')
                                ->body('Tidak dapat menghapus data, karena akan menyebabkan stok barang menjadi negatif.')
                                ->send();
                        }
                    });
                }),
            Actions\ForceDeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array 
    {
        // Menangani perubahan jumlah barang masuk
        if ($this->record->jumlah_barang_masuk != $data['jumlah_barang_masuk']) {
            // Hitung selisih
            $selisih = $data['jumlah_barang_masuk'] - $this->record->jumlah_barang_masuk;
            
            // Update stok barang
            DB::transaction(function () use ($selisih) {
                $barang = Barang::findOrFail($this->record->barang_id);
                $barang->increment('jumlah_barang', $selisih);
            });
        }
        
        return $data;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Barang Masuk diperbarui')
            ->body('Data barang masuk berhasil diperbarui dan stok telah diperbarui.');
    }
}