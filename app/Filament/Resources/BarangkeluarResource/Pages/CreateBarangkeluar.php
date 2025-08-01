<?php

namespace App\Filament\Resources\BarangkeluarResource\Pages;

use App\Filament\Resources\BarangkeluarResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Barang;
use App\Models\Barangkeluardetail;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateBarangkeluar extends CreateRecord
{
    protected static string $resource = BarangkeluarResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Ambil data barang_items dari repeater
            $barangItems = $data['barang_items'] ?? [];

            // Hapus barang_items dari data utama
            unset($data['barang_items']);

            $createdRecords = [];

            foreach ($barangItems as $item) {
                // Gabungkan data utama dengan data dari setiap item
                $recordData = array_merge($data, [
                    'barang_id' => $item['barang_id'],
                    'kategori_id' => $item['kategori_id'],
                    'subkategori_id' => $item['subkategori_id'],
                    'jumlah_barang_keluar' => $item['jumlah_barang_keluar'],
                ]);

                // Buat record barang keluar
                $barangkeluar = static::getModel()::create($recordData);

                // Update stok barang
                $barang = \App\Models\Barang::find($item['barang_id']);
                if ($barang && $barang->jumlah_barang >= $item['jumlah_barang_keluar']) {
                    $barang->update([
                        'jumlah_barang' => $barang->jumlah_barang - $item['jumlah_barang_keluar']
                    ]);
                }

                $createdRecords[] = $barangkeluar;
            }

            // Return record pertama (untuk compatibility dengan Filament)
            return $createdRecords[0] ?? static::getModel()::create($data);
        });
    }

    // Static method untuk validasi stok sebelum submit
    public static function validateStock(array $barangItems): array
    {
        $errors = [];

        foreach ($barangItems as $index => $item) {
            if (isset($item['barang_id']) && isset($item['jumlah_barang_keluar'])) {
                $barang = Barang::find($item['barang_id']);

                if ($barang && $item['jumlah_barang_keluar'] > $barang->jumlah_barang) {
                    $errors["barang_items.{$index}.jumlah_barang_keluar"] = "Stok tidak mencukupi. Tersedia: {$barang->jumlah_barang}, diminta: {$item['jumlah_barang_keluar']}";
                }
            }
        }

        return $errors;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
