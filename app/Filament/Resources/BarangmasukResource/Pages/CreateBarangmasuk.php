<?php

namespace App\Filament\Resources\BarangmasukResource\Pages;

use App\Filament\Resources\BarangmasukResource;
use App\Models\Barang;
use App\Models\Barangmasuk;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateBarangmasuk extends CreateRecord
{
    protected static string $resource = BarangmasukResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!isset($data['total_harga'])) {
            $data['total_harga'] = $data['harga_barang'] * $data['jumlah_barang_masuk'];
        }
        
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Menyimpan data barang masuk
            $barangMasuk = new Barangmasuk();
            $barangMasuk->fill($data);
            
            // Penanganan berdasarkan tipe transaksi
            if ($data['tipe_transaksi'] === 'barang_baru') {
                // Membuat barang baru
                $barangBaru = Barang::create([
                    'serial_number' => $data['serial_number'],
                    'kode_barang' => $data['kode_barang'],
                    'nama_barang' => $data['nama_barang'],
                    'jumlah_barang' => $data['jumlah_barang_masuk'],
                    'kategori_id' => $data['kategori_id'],
                    'harga_barang' => $data['harga_barang'],
                ]);
                
                // Update barang_id di barang masuk
                $barangMasuk->barang_id = $barangBaru->id;
            } else {
                // Update stok barang yang sudah ada
                $barang = Barang::findOrFail($data['barang_id']);
                $barang->increment('jumlah_barang', $data['jumlah_barang_masuk']);
                
                // Update harga barang jika berubah
                if (isset($data['harga_barang']) && $data['harga_barang'] != $barang->harga_barang) {
                    $barang->update(['harga_barang' => $data['harga_barang']]);
                }
            }
            
            $barangMasuk->save();
            return $barangMasuk;
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Barang Masuk ditambahkan')
            ->body('Data barang masuk berhasil ditambahkan dan stok telah diperbarui.');
    }

    public function getTitle(): string|Htmlable
    {
        return 'Input barang masuk';
    }
}