<?php

namespace App\Filament\Resources\BarangKeluarResource\Pages;

use App\Filament\Resources\BarangKeluarResource;
use App\Models\Barang;
use App\Models\BarangKeluar;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateBarangKeluar extends CreateRecord
{
    protected static string $resource = BarangKeluarResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set sumber sebagai manual untuk input langsung
        $data['sumber'] = 'manual';
        
        // Set user_id jika belum ada
        if (!isset($data['user_id'])) {
            $data['user_id'] = filament()->auth()->id();
        }

        // PENTING: Set pengajuan_id sebagai null untuk input manual
        if (!isset($data['pengajuan_id']) || empty($data['pengajuan_id'])) {
            $data['pengajuan_id'] = null;
        }

        // Remove fields yang tidak ada di database
        $fieldsToRemove = [
            'stok_tersedia',
            'sisa_stok_display', 
            'nama_barang_display',
            'kode_barang_display',
            'barang_items' // Jika menggunakan repeater
        ];

        foreach ($fieldsToRemove as $field) {
            unset($data[$field]);
        }

        // Log data untuk debugging
        Log::info('Data before create:', $data);

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            // Validasi stok sebelum membuat record
            $barang = Barang::find($data['barang_id']);
            
            if (!$barang) {
                Notification::make()
                    ->title('Error!')
                    ->body('Barang tidak ditemukan')
                    ->danger()
                    ->send();
                
                $this->halt();
            }

            if ($barang->jumlah_barang < $data['jumlah_barang_keluar']) {
                Notification::make()
                    ->title('Stok Tidak Mencukupi!')
                    ->body("Stok tersedia hanya {$barang->jumlah_barang} unit")
                    ->danger()
                    ->persistent()
                    ->send();
                
                $this->halt();
            }

            // Gunakan database transaction untuk keamanan
            return DB::transaction(function () use ($data, $barang) {
                // Buat record barang keluar dengan pengajuan_id = null
                $barangKeluar = BarangKeluar::create($data);

                // Kurangi stok barang
                $barang->decrement('jumlah_barang', $data['jumlah_barang_keluar']);

                // Log successful creation
                Log::info('Barang keluar created successfully:', [
                    'id' => $barangKeluar->id,
                    'barang_id' => $barangKeluar->barang_id,
                    'pengajuan_id' => $barangKeluar->pengajuan_id, // Should be null
                    'jumlah' => $barangKeluar->jumlah_barang_keluar
                ]);

                // Kirim notifikasi sukses
                Notification::make()
                    ->title('Barang Keluar Berhasil Dicatat!')
                    ->body("Barang {$barang->nama_barang} sebanyak {$data['jumlah_barang_keluar']} unit telah dikeluarkan")
                    ->success()
                    ->send();

                // Peringatan jika stok rendah setelah transaksi  
                $sisaStok = $barang->fresh()->jumlah_barang;
                if ($sisaStok <= 5) {
                    Notification::make()
                        ->title('Peringatan Stok Rendah!')
                        ->body("Sisa stok {$barang->nama_barang} tinggal {$sisaStok} unit")
                        ->warning()
                        ->persistent()
                        ->send();
                }

                return $barangKeluar;
            });

        } catch (\Exception $e) {
            Log::error('Error creating barang keluar:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            
            Notification::make()
                ->title('Terjadi Kesalahan!')
                ->body('Gagal menyimpan data barang keluar. Silakan coba lagi atau hubungi administrator.')
                ->danger()
                ->persistent()
                ->send();
            
            $this->halt();
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Barang keluar berhasil dicatat';
    }
}