<?php

namespace App\Filament\Resources\BarangmasukResource\Pages;

use App\Filament\Resources\BarangmasukResource;
use App\Models\Barang;
use App\Models\Barangmasuk;
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
            // Get the data for processing
            $tipeTransaksi = $data['tipe_transaksi'];

            // Default project_name jika tidak ada
            if ($data['status'] !== 'project' || empty($data['project_name'])) {
                $data['project_name'] = '-'; // Nilai default
            }

            // Siapkan data user dan tanggal
            $baseData = [
                'tanggal_barang_masuk' => $data['tanggal_barang_masuk'],
                'status' => $data['status'],
                'project_name' => $data['project_name'],
                'dibeli' => $data['dibeli'],
                'user_id' => $data['user_id'],
            ];

            $createdRecords = [];

            if ($tipeTransaksi === 'barang_baru') {
                // Proses multiple barang baru dari repeater
                foreach ($data['barang_baru_items'] as $barangBaruItem) {
                    // Buat barang baru terlebih dahulu
                    $barang = Barang::create([
                        'serial_number' => $barangBaruItem['serial_number'],
                        'kode_barang' => $barangBaruItem['kode_barang'],
                        'nama_barang' => $barangBaruItem['nama_barang'],
                        'jumlah_barang' => $barangBaruItem['jumlah_barang_masuk'], // Set stok awal
                        'kategori_id' => $barangBaruItem['kategori_id'],
                    ]);

                    // Buat record BarangMasuk untuk barang ini
                    $barangMasukData = array_merge($baseData, [
                        'barang_id' => $barang->id,
                        'jumlah_barang_masuk' => $barangBaruItem['jumlah_barang_masuk'],
                        'kategori_id' => $barangBaruItem['kategori_id'],
                    ]);

                    $createdRecords[] = static::getModel()::create($barangMasukData);
                }

                // Notifikasi sukses
                Notification::make()
                    ->title(count($data['barang_baru_items']) . ' barang baru berhasil ditambahkan')
                    ->success()
                    ->send();
            } else {
                // Proses multiple barang lama dari repeater
                foreach ($data['barang_lama_items'] as $barangLamaItem) {
                    // Dapatkan data barang yang ada
                    $barang = Barang::findOrFail($barangLamaItem['barang_id']);

                    // Update jumlah stok
                    $barang->jumlah_barang += $barangLamaItem['jumlah_barang_masuk'];
                    $barang->save();

                    // Buat record BarangMasuk untuk barang ini
                    $barangMasukData = array_merge($baseData, [
                        'barang_id' => $barangLamaItem['barang_id'],
                        'jumlah_barang_masuk' => $barangLamaItem['jumlah_barang_masuk'],
                        'kategori_id' => $barang->kategori_id, // Ambil dari barang yang sudah ada
                    ]);

                    $createdRecords[] = static::getModel()::create($barangMasukData);
                }

                // Notifikasi sukses
                Notification::make()
                    ->title(count($data['barang_lama_items']) . ' stok barang berhasil diperbarui')
                    ->success()
                    ->send();
            }

            DB::commit();

            // Kembalikan record pertama (untuk tujuan redirect)
            return $createdRecords[0] ?? static::getModel()::latest()->first();
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->danger()
                ->title('Terjadi kesalahan')
                ->body($e->getMessage())
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
        return Notification::make()
            ->title('Barang Masuk Berhasil Ditambahkan') 
            ->success()
            ->icon('heroicon-o-check')
            ->body('Data barang masuk telah berhasil ditambahkan.');
    }
}
