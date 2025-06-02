<?php

namespace App\Filament\Resources\BarangkeluarResource\Pages;

use App\Filament\Resources\BarangkeluarResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Barang;

class CreateBarangkeluar extends CreateRecord
{
    protected static string $resource = BarangkeluarResource::class;
    
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Ambil data dari repeater barang_items
        $barangItems = $data['barang_items'] ?? [];
        
        // Hapus barang_items dari data utama
        unset($data['barang_items']);
        
        // Buat record untuk setiap item barang
        $createdRecords = [];
        
        foreach ($barangItems as $item) {
            // Merge data utama dengan data item
            $recordData = array_merge($data, [
                'barang_id' => $item['barang_id'],
                'jumlah_barang_keluar' => $item['jumlah_barang_keluar'],
            ]);
            
            // Buat record barang keluar
            $record = static::getModel()::create($recordData);
            $createdRecords[] = $record;
            
            // Update stok barang
            $barang = Barang::find($item['barang_id']);
            if ($barang) {
                $barang->jumlah_barang -= $item['jumlah_barang_keluar'];
                $barang->save();
            }
        }
        
        // Return record pertama (untuk keperluan redirect)
        return $createdRecords[0] ?? static::getModel()::create($data);
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}