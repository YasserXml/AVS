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
            $jumlahBarangMasuk = $data['jumlah_barang_masuk'];

            if ($data['status'] !== 'project') {
                $data['project_name'] = '-'; // atau nilai default lainnya seperti 'Non-Project'
            }

            // Persiapkan data untuk barangmasuks
            $dataBarangMasuk = [
                'tanggal_barang_masuk' => $data['tanggal_barang_masuk'],
                'status' => $data['status'],
                'project_name' => $data['project_name'],
                'dibeli' => $data['dibeli'],
                'user_id' => $data['user_id'],
                'jumlah_barang_masuk' => $jumlahBarangMasuk,
                'keterangan' => isset($data['keterangan']) ? $data['keterangan'] : null,
            ];

            // Handle barang_baru (create new item)
            if ($tipeTransaksi === 'barang_baru') {
                // Create a new barang record first
                $barang = Barang::create([
                    'serial_number' => $data['serial_number'],
                    'kode_barang' => $data['kode_barang'],
                    'nama_barang' => $data['nama_barang'],
                    'jumlah_barang' => $jumlahBarangMasuk, // Set initial stock
                    'kategori_id' => $data['kategori_id'],
                ]);

                // Update data with the newly created barang_id and kategori_id
                $dataBarangMasuk['barang_id'] = $barang->id;
                $dataBarangMasuk['kategori_id'] = $data['kategori_id'];
                // Set the barang_id and kategori_id in the dataBarangMasuk array

                // Create the barangmasuk record
                $barangMasuk = static::getModel()::create($dataBarangMasuk);

                // Optional notification
                Notification::make()
                    ->title('Barang baru berhasil ditambahkan')
                    ->success()
                    ->send();
            } else {
                // Handle barang_lama (update existing item)
                $barang = Barang::findOrFail($data['barang_id']);

                // Update the stock quantity
                $barang->jumlah_barang += $jumlahBarangMasuk;
                $barang->save();

                // Add barang_id and kategori_id to data
                $dataBarangMasuk['barang_id'] = $data['barang_id'];
                $dataBarangMasuk['kategori_id'] = $barang->kategori_id; // Ambil dari barang yang sudah ada

                // Create the barangmasuk record
                $barangMasuk = static::getModel()::create($dataBarangMasuk);

                // Optional notification
                Notification::make()
                    ->title('Stok barang berhasil diperbarui')
                    ->success()
                    ->send();
            }

            DB::commit();
            return $barangMasuk;
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
