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

            // Siapkan spesifikasi berdasarkan kategori
            $spesifikasi = [];
            $kategori = Kategori::find($data['kategori_id']);

            if ($kategori) {
                $kategoriNama = strtolower($kategori->nama_kategori);

                if ($kategoriNama === 'komputer') {
                    $spesifikasi = [
                        'processor' => $data['spec_processor'] ?? null,
                        'ram' => $data['spec_ram'] ?? null,
                        'storage' => $data['spec_storage'] ?? null,
                        'vga' => $data['spec_vga'] ?? null,
                        'motherboard' => $data['spec_motherboard'] ?? null,
                        'psu' => $data['spec_psu'] ?? null,
                    ];
                } elseif ($kategoriNama === 'elektronik') {
                    $spesifikasi = [
                        'brand' => $data['spec_brand'] ?? null,
                        'model' => $data['spec_model'] ?? null,
                        'garansi' => $data['spec_garansi'] ?? null,
                    ];
                } elseif ($kategoriNama === 'furniture') {
                    $spesifikasi = [
                        'material' => $data['spec_material'] ?? null,
                        'dimensi' => $data['spec_dimensi'] ?? null,
                        'warna' => $data['spec_warna'] ?? null,
                        'berat' => $data['spec_berat'] ?? null,
                        'finishing' => $data['spec_finishing'] ?? null,
                        'kondisi' => $data['spec_kondisi'] ?? null,
                    ];
                }
            }

            // Filter spesifikasi yang tidak null atau kosong
            $spesifikasi = array_filter($spesifikasi, fn($value) => !is_null($value) && $value !== '');

            // Buat barang baru
            $barang = Barang::create([
                'serial_number' => $data['serial_number'],
                'kode_barang' => $data['kode_barang'],
                'nama_barang' => $data['nama_barang'],
                'jumlah_barang' => $data['jumlah_barang_masuk'],
                'kategori_id' => $data['kategori_id'],
                'subkategori_id' => $data['subkategori_id'] ?? null,
                'spesifikasi' => !empty($spesifikasi) ? $spesifikasi : null,
            ]);

            // Buat record BarangMasuk
            $barangMasuk = static::getModel()::create([
                'user_id' => $data['user_id'],
                'barang_id' => $barang->id,
                'jumlah_barang_masuk' => $data['jumlah_barang_masuk'],
                'tanggal_barang_masuk' => $data['tanggal_barang_masuk'],
                'status' => $data['status'],
                'dibeli' => $data['dibeli'],
                'project_name' => $data['project_name'],
                'kategori_id' => $data['kategori_id'],
            ]);

            DB::commit();

            // Notifikasi sukses
            Notification::make()
                ->title('Barang berhasil ditambahkan')
                ->body("Barang '{$data['nama_barang']}' dengan serial number '{$data['serial_number']}' berhasil ditambahkan ke inventori dengan jumlah {$data['jumlah_barang_masuk']} unit.")
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Konversi field spesifikasi flat ke struktur JSON
        $spesifikasi = [];

        // Ambil semua field yang dimulai dengan 'spec_'
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'spec_')) {
                $specKey = str_replace('spec_', '', $key);
                if (!empty($value)) {
                    $spesifikasi[$specKey] = $value;
                }
                // Hapus field flat dari data utama
                unset($data[$key]);
            }
        }

        // Set spesifikasi ke data untuk disimpan di barang nanti
        $data['spesifikasi'] = !empty($spesifikasi) ? $spesifikasi : null;

        return $data;
    }
}
