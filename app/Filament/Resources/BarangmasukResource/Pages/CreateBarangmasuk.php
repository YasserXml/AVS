<?php

namespace App\Filament\Resources\BarangmasukResource\Pages;

use App\Filament\Resources\BarangmasukResource;
use App\Models\Barang;
use App\Models\Barangmasuk;
use App\Models\Kategori;
use Exception;
use Filament\Actions;
use Filament\Forms\Components\Group;
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

            $kategori = Kategori::find($data['kategori_id']);
            $barangMasukRecords = collect();
            $totalBarang = 0;

            // Loop untuk setiap item dalam repeater
            foreach ($data['items'] as $itemData) {
                // Siapkan spesifikasi berdasarkan kategori
                $spesifikasi = [];

                if ($kategori) {
                    $kategoriNama = strtolower($kategori->nama_kategori);

                    if ($kategoriNama === 'komputer') {
                        $spesifikasi = [
                            'processor' => $itemData['spec_processor'] ?? null,
                            'ram' => $itemData['spec_ram'] ?? null,
                            'storage' => $itemData['spec_storage'] ?? null,
                            'vga' => $itemData['spec_vga'] ?? null,
                            'motherboard' => $itemData['spec_motherboard'] ?? null,
                            'psu' => $itemData['spec_psu'] ?? null,
                        ];
                    } elseif ($kategoriNama === 'elektronik') {
                        $spesifikasi = [
                            'brand' => $itemData['spec_brand'] ?? null,
                            'model' => $itemData['spec_model'] ?? null,
                            'garansi' => $itemData['spec_garansi'] ?? null,
                        ];
                    } elseif ($kategoriNama === 'furniture') {
                        $spesifikasi = [
                            'material' => $itemData['spec_material'] ?? null,
                            'dimensi' => $itemData['spec_dimensi'] ?? null,
                            'warna' => $itemData['spec_warna'] ?? null,
                            'berat' => $itemData['spec_berat'] ?? null,
                            'finishing' => $itemData['spec_finishing'] ?? null,
                            'kondisi' => $itemData['spec_kondisi'] ?? null,
                        ];
                    }
                }

                // Filter spesifikasi yang tidak null atau kosong
                $spesifikasi = array_filter($spesifikasi, fn($value) => !is_null($value) && $value !== '');

                // Buat barang baru untuk setiap item
                $barang = Barang::create([
                    'serial_number' => $itemData['serial_number'],
                    'kode_barang' => $itemData['kode_barang'],
                    'nama_barang' => $itemData['nama_barang'],
                    'jumlah_barang' => 1, // Setiap item adalah 1 unit
                    'kategori_id' => $data['kategori_id'],
                    'subkategori_id' => $data['subkategori_id'] ?? null,
                    'spesifikasi' => !empty($spesifikasi) ? $spesifikasi : null,
                ]);

                // Buat record BarangMasuk untuk setiap barang
                $barangMasuk = static::getModel()::create([
                    'user_id' => $data['user_id'],
                    'barang_id' => $barang->id,
                    'jumlah_barang_masuk' => 1, // Setiap item adalah 1 unit
                    'tanggal_barang_masuk' => $data['tanggal_barang_masuk'],
                    'status' => $data['status'],
                    'dibeli' => $data['dibeli'],
                    'project_name' => $data['project_name'],
                    'kategori_id' => $data['kategori_id'],
                    'subkategori_id' => $data['subkategori_id'] ?? null,
                ]);

                $barangMasukRecords->push($barangMasuk);
                $totalBarang++;
            }

            DB::commit();

            // Notifikasi sukses dengan detail total barang
            $namaBarangList = collect($data['items'])->pluck('nama_barang')->join(', ');

            Notification::make()
                ->title('Barang berhasil ditambahkan')
                ->body("Berhasil menambahkan {$totalBarang} barang ke inventori: {$namaBarangList}")
                ->success()
                ->duration(5000)
                ->send();

            // Return salah satu record BarangMasuk (untuk redirect)
            return $barangMasukRecords->first();
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
        // Validasi bahwa items ada dan tidak kosong
        if (!isset($data['items']) || empty($data['items'])) {
            throw new \Exception('Data barang tidak boleh kosong.');
        }

        // Validasi jumlah items sesuai dengan jumlah yang diinput
        $expectedCount = intval($data['jumlah_barang_masuk'] ?? 0);
        $actualCount = count($data['items']);

        if ($actualCount !== $expectedCount) {
            throw new \Exception("Jumlah detail barang ({$actualCount}) tidak sesuai dengan jumlah yang diinput ({$expectedCount}).");
        }

        // Validasi serial number unik
        $serialNumbers = collect($data['items'])->pluck('serial_number')->filter();
        $duplicates = $serialNumbers->duplicates();

        if ($duplicates->isNotEmpty()) {
            throw new \Exception('Serial number tidak boleh duplikat: ' . $duplicates->join(', '));
        }

        // Validasi serial number belum ada di database
        foreach ($serialNumbers as $serialNumber) {
            if (Barang::where('serial_number', $serialNumber)->exists()) {
                throw new \Exception("Serial number '{$serialNumber}' sudah ada dalam database.");
            }
        }

        return $data;
    }

    // Method untuk validasi form
    protected function getFormSchema(): array
    {
        return [
            Group::make()
                ->afterStateUpdated(function (callable $get, callable $set) {
                    $jumlah = intval($get('jumlah_barang_masuk') ?? 0);
                    $items = $get('items') ?? [];

                    // Pastikan jumlah items sesuai dengan jumlah yang diinput
                    if (count($items) !== $jumlah) {
                        $newItems = [];
                        for ($i = 0; $i < $jumlah; $i++) {
                            $newItems[] = $items[$i] ?? [
                                'serial_number' => '',
                                'kode_barang' => '',
                                'nama_barang' => '',
                            ];
                        }
                        $set('items', $newItems);
                    }
                })
        ];
    }
}
