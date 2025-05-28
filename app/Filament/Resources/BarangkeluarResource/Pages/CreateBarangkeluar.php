<?php

use App\Filament\Resources\BarangkeluarResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Barang;
use App\Models\BarangKeluar;

class CreateBarangKeluar extends CreateRecord
{
    protected static string $resource = BarangkeluarResource::class;

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
        $data['pengajuan_id'] = null;

        // Log data untuk debugging
        Log::info('Data sebelum diproses:', $data);

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            // Validasi apakah ada barang_items
            if (!isset($data['barang_items']) || empty($data['barang_items'])) {
                Notification::make()
                    ->title('Error!')
                    ->body('Tidak ada barang yang dipilih untuk dikeluarkan')
                    ->danger()
                    ->send();
                
                $this->halt();
            }

            // Gunakan database transaction untuk keamanan
            return DB::transaction(function () use ($data) {
                $createdRecords = [];
                
                // Loop setiap barang dalam repeater
                foreach ($data['barang_items'] as $itemData) {
                    // Validasi data item
                    if (!isset($itemData['barang_id']) || !isset($itemData['jumlah_barang_keluar'])) {
                        continue; // Skip item yang tidak lengkap
                    }

                    // Validasi stok
                    $barang = Barang::find($itemData['barang_id']);
                    
                    if (!$barang) {
                        Notification::make()
                            ->title('Error!')
                            ->body('Barang tidak ditemukan')
                            ->danger()
                            ->send();
                        
                        throw new \Exception('Barang tidak ditemukan');
                    }

                    if ($barang->jumlah_barang < $itemData['jumlah_barang_keluar']) {
                        Notification::make()
                            ->title('Stok Tidak Mencukupi!')
                            ->body("Stok {$barang->nama_barang} tersedia hanya {$barang->jumlah_barang} unit")
                            ->danger()
                            ->persistent()
                            ->send();
                        
                        throw new \Exception('Stok tidak mencukupi');
                    }

                    // Siapkan data untuk create
                    $createData = [
                        'barang_id' => $itemData['barang_id'],
                        'jumlah_barang_keluar' => $itemData['jumlah_barang_keluar'],
                        'tanggal_keluar_barang' => $data['tanggal_keluar_barang'],
                        'status' => $data['status'],
                        'user_id' => $data['user_id'],
                        'keterangan' => $data['keterangan'] ?? null,
                        'project_name' => $data['project_name'] ?? null,
                        'sumber' => $data['sumber'],
                        'pengajuan_id' => $data['pengajuan_id'],
                    ];

                    // Buat record barang keluar
                    $barangKeluar = BarangKeluar::create($createData);
                    $createdRecords[] = $barangKeluar;

                    // Kurangi stok barang
                    $barang->decrement('jumlah_barang', $itemData['jumlah_barang_keluar']);

                    // Log successful creation
                    Log::info('Barang keluar berhasil dibuat:', [
                        'id' => $barangKeluar->id,
                        'barang_id' => $barangKeluar->barang_id,
                        'nama_barang' => $barang->nama_barang,
                        'jumlah' => $barangKeluar->jumlah_barang_keluar
                    ]);

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
                }

                // Validasi apakah ada record yang berhasil dibuat
                if (empty($createdRecords)) {
                    throw new \Exception('Tidak ada barang yang berhasil diproses');
                }

                // Kirim notifikasi sukses
                $totalItems = count($createdRecords);
                Notification::make()
                    ->title('Barang Keluar Berhasil Dicatat!')
                    ->body("Berhasil mencatat {$totalItems} item barang keluar")
                    ->success()
                    ->send();

                // Return record pertama (untuk kompatibilitas dengan Filament)
                return $createdRecords[0];
            });

        } catch (\Exception $e) {
            Log::error('Error membuat barang keluar:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            
            Notification::make()
                ->title('Terjadi Kesalahan!')
                ->body('Gagal menyimpan data barang keluar. ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->send();
            
            // Return model kosong jika terjadi error
            return new BarangKeluar();
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Barang keluar berhasil dicatat';
    }
}