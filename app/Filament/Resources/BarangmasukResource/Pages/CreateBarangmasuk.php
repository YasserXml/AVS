<?php

namespace App\Filament\Resources\BarangmasukResource\Pages;

use App\Filament\Resources\BarangmasukResource;
use App\Models\Barang;
use App\Models\Barangmasuk;
use App\Models\Kategori;
use Exception;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateBarangmasuk extends CreateRecord
{
    protected static string $resource = BarangmasukResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        DB::beginTransaction();
        try {
            // Default project_name jika tidak ada
            if ($data['status'] !== 'project' || empty($data['project_name'])) {
                $data['project_name'] = '-';
            }

            $barang = null;
            $isBarangBaru = $data['jenis_barang'] === 'barang_baru';

            if ($isBarangBaru) {
                // === BARANG BARU ===
                // Siapkan spesifikasi berdasarkan kategori
                $spesifikasi = [];
                $kategori = Kategori::find($data['kategori_id']);

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

                // Buat barang baru
                $barang = Barang::create([
                    'serial_number' => $data['serial_number'],
                    'kode_barang' => $data['kode_barang'],
                    'nama_barang' => $data['nama_barang'],
                    'jumlah_barang' => $data['jumlah_barang_masuk'],
                    'kategori_id' => $data['kategori_id'],
                    'spesifikasi' => !empty($spesifikasi) ? $spesifikasi : null,
                ]);

                $pesanNotifikasi = "Barang baru '{$data['nama_barang']}' dengan serial number '{$data['serial_number']}' berhasil ditambahkan ke inventori.";
            } else {
                // === BARANG EXISTING ===
                // Ambil barang existing sebagai referensi
                $barangExisting = Barang::find($data['barang_existing_id']);

                if (!$barangExisting) {
                    throw new Exception('Barang existing tidak ditemukan');
                }

                // Siapkan spesifikasi - ambil dari existing atau dari form jika ada perubahan
                $spesifikasi = [];
                $kategoriNama = strtolower($barangExisting->kategori->nama_kategori);

                if ($kategoriNama === 'komputer') {
                    $spesifikasi = [
                        'processor' => $data['spec_processor'] ?? $barangExisting->spesifikasi['processor'] ?? null,
                        'ram' => $data['spec_ram'] ?? $barangExisting->spesifikasi['ram'] ?? null,
                        'storage' => $data['spec_storage'] ?? $barangExisting->spesifikasi['storage'] ?? null,
                        'vga' => $data['spec_vga'] ?? $barangExisting->spesifikasi['vga'] ?? null,
                        'motherboard' => $data['spec_motherboard'] ?? $barangExisting->spesifikasi['motherboard'] ?? null,
                        'psu' => $data['spec_psu'] ?? $barangExisting->spesifikasi['psu'] ?? null,
                    ];
                } elseif ($kategoriNama === 'elektronik') {
                    $spesifikasi = [
                        'brand' => $data['spec_brand'] ?? $barangExisting->spesifikasi['brand'] ?? null,
                        'model' => $data['spec_model'] ?? $barangExisting->spesifikasi['model'] ?? null,
                        'garansi' => $data['spec_garansi'] ?? $barangExisting->spesifikasi['garansi'] ?? null,
                    ];
                }

                // Filter spesifikasi yang tidak null
                $spesifikasi = array_filter($spesifikasi, fn($value) => !is_null($value) && $value !== '');

                // Buat barang baru dengan nama sama tapi serial number berbeda
                $barang = Barang::create([
                    'serial_number' => $data['serial_number'],
                    'kode_barang' => $data['kode_barang'],
                    'nama_barang' => $barangExisting->nama_barang, // Ambil dari existing
                    'jumlah_barang' => $data['jumlah_barang_masuk'],
                    'kategori_id' => $barangExisting->kategori_id, // Ambil dari existing
                    'spesifikasi' => !empty($spesifikasi) ? $spesifikasi : null,
                ]);

                // Update jumlah barang existing (menambah stock total untuk nama barang yang sama)
                $barangExisting->increment('jumlah_barang', $data['jumlah_barang_masuk']);

                $pesanNotifikasi = "Unit baru '{$barangExisting->nama_barang}' dengan serial number '{$data['serial_number']}' berhasil ditambahkan. Total stock sekarang: " . ($barangExisting->jumlah_barang + $data['jumlah_barang_masuk']) . " unit.";
            }

            // Buat record BarangMasuk
            $barangMasuk = static::getModel()::create([
                'user_id' => $data['user_id'],
                'barang_id' => $barang->id,
                'jumlah_barang_masuk' => $data['jumlah_barang_masuk'],
                'tanggal_barang_masuk' => $data['tanggal_barang_masuk'],
                'status' => $data['status'],
                'dibeli' => $data['dibeli'],
                'project_name' => $data['project_name'],
                'kategori_id' => $barang->kategori_id,
            ]);

            DB::commit();

            // Notifikasi sukses
            Notification::make()
                ->title($isBarangBaru ? 'Barang baru berhasil ditambahkan' : 'Unit barang existing berhasil ditambahkan')
                ->body($pesanNotifikasi)
                ->success()
                ->duration(5000)
                ->send();

            return $barangMasuk;
        } catch (Exception $e) {
            DB::rollBack();

            Notification::make()
                ->danger()
                ->title('Terjadi kesalahan')
                ->body('Error: ' . $e->getMessage())
                ->duration(10000)
                ->send();

            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Tambah Barang Masuk';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Simpan Data')
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

    protected function getCreatedNotification(): ?Notification
    {
        return null; // Kita sudah handle notifikasi di handleRecordCreation
    }
}
