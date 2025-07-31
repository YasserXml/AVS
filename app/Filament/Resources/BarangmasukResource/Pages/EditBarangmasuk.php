<?php

namespace App\Filament\Resources\BarangmasukResource\Pages;

use App\Filament\Resources\BarangmasukResource;
use App\Models\Barang;
use App\Models\Barangmasuk;
use App\Models\Kategori;
use Filament\Actions;
use Filament\Actions\ForceDeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditBarangmasuk extends EditRecord
{
    protected static string $resource = BarangmasukResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load spesifikasi dari barang untuk form edit
        if (isset($data['barang_id'])) {
            $barang = Barang::find($data['barang_id']);
            if ($barang && $barang->spesifikasi) {
                $spesifikasi = is_string($barang->spesifikasi) 
                    ? json_decode($barang->spesifikasi, true) 
                    : $barang->spesifikasi;

                // Map spesifikasi ke form fields
                if (is_array($spesifikasi)) {
                    $data['spec_processor'] = $spesifikasi['processor'] ?? null;
                    $data['spec_ram'] = $spesifikasi['ram'] ?? null;
                    $data['spec_storage'] = $spesifikasi['storage'] ?? null;
                    $data['spec_vga'] = $spesifikasi['vga'] ?? null;
                    $data['spec_motherboard'] = $spesifikasi['motherboard'] ?? null;
                    $data['spec_psu'] = $spesifikasi['psu'] ?? null;
                    $data['spec_brand'] = $spesifikasi['brand'] ?? null;
                    $data['spec_model'] = $spesifikasi['model'] ?? null;
                    $data['spec_garansi'] = $spesifikasi['garansi'] ?? null;
                }
            }

            // Load data barang lainnya
            if ($barang) {
                $data['serial_number'] = $barang->serial_number;
                $data['kode_barang'] = $barang->kode_barang;
                $data['nama_barang'] = $barang->nama_barang;
            }
        }

        return $data;
    }

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
            ForceDeleteAction::make(),
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

        // Update spesifikasi barang jika ada perubahan
        $barang = Barang::findOrFail($this->record->barang_id);
        $kategori = Kategori::find($data['kategori_id']);
        
        $spesifikasi = [];
        if ($kategori && strtolower($kategori->nama_kategori) === 'komputer') {
            $spesifikasi = [
                'processor' => $data['spec_processor'] ?? null,
                'ram' => $data['spec_ram'] ?? null,
                'storage' => $data['spec_storage'] ?? null,
                'vga' => $data['spec_vga'] ?? null,
                'motherboard' => $data['spec_motherboard'] ?? null,
                'psu' => $data['spec_psu'] ?? null,
            ];
        } elseif ($kategori && strtolower($kategori->nama_kategori) === 'elektronik') {
            $spesifikasi = [
                'brand' => $data['spec_brand'] ?? null,
                'model' => $data['spec_model'] ?? null,
                'garansi' => $data['spec_garansi'] ?? null,
            ];
        }

        // Filter spesifikasi yang tidak null
        $spesifikasi = array_filter($spesifikasi, fn($value) => !is_null($value) && $value !== '');

        // Update data barang
        $barang->update([
            'serial_number' => $data['serial_number'],
            'kode_barang' => $data['kode_barang'],
            'nama_barang' => $data['nama_barang'],
            'kategori_id' => $data['kategori_id'],
            'spesifikasi' => !empty($spesifikasi) ? $spesifikasi : null,
        ]);

        // Default project_name jika tidak ada
        if ($data['status'] !== 'project' || empty($data['project_name'])) {
            $data['project_name'] = '-';
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