<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use App\Models\Barang;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreatePengajuan extends CreateRecord
{
    protected static string $resource = PengajuanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pengajuan barang berhasil dibuat';
    }

    protected function handleRecordCreation(array $data): Model
    {
        DB::beginTransaction();
        try {
            // Debug untuk melihat data yang diterima
            // dd($data); // Uncomment untuk debug

            // Pastikan status_barang ada dan tidak null
            if (!isset($data['status_barang']) || $data['status_barang'] === null) {
                $data['status_barang'] = 'oprasional_kantor'; // Set default value
            }

            // Data dasar untuk semua pengajuan
            $baseData = [
                'user_id' => $data['user_id'],
                'tanggal_pengajuan' => $data['tanggal_pengajuan'],
                'status_barang' => $data['status_barang'], // Pastikan nilai ini ada
                'keterangan' => $data['keterangan'] ?? null,
                'status' => 'pending', // Default status
            ];

            $createdRecords = [];

            // Proses data dari repeater dan buat multiple records
            if (isset($data['detail_pengajuan']) && count($data['detail_pengajuan']) > 0) {
                foreach ($data['detail_pengajuan'] as $detailItem) {
                    // Pastikan barang_id ada
                    if (!isset($detailItem['barang_id']) || empty($detailItem['barang_id'])) {
                        continue;
                    }

                    // Dapatkan barang yang dipilih
                    $barang = Barang::findOrFail($detailItem['barang_id']);

                    
                    // Buat data pengajuan untuk barang ini
                    $pengajuanData = array_merge($baseData, [
                        'barang_id' => $detailItem['barang_id'],
                        'kategoris_id' => $detailItem['kategoris_id'] ?? $barang->kategori_id, // Gunakan nilai dari form dulu
                        'Jumlah_barang_diajukan' => $detailItem['jumlah_diajukan'],
                    ]);

                    // Tambahkan catatan ke keterangan jika ada
                    if (!empty($detailItem['catatan'])) {
                        $pengajuanData['keterangan'] = (empty($pengajuanData['keterangan']) ? '' : $pengajuanData['keterangan'] . "\n\n") .
                            "Catatan barang: " . $detailItem['catatan'];
                    }

                    // Simpan record pengajuan
                    $createdRecords[] = static::getModel()::create($pengajuanData);
                }

                // Notifikasi sukses
                Notification::make()
                    ->title('Pengajuan barang berhasil dibuat')
                    ->body('Sebanyak ' . count($createdRecords) . ' barang telah diajukan')
                    ->success()
                    ->send();
            } else {
                // Jika tidak ada detail barang
                Notification::make()
                    ->title('Pengajuan tidak berhasil')
                    ->body('Tidak ada barang yang diajukan')
                    ->warning()
                    ->send();

                throw new \Exception('Tidak ada barang yang diajukan');
            }

            DB::commit();

            // Kembalikan record pertama untuk redirect
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



    public function getTitle(): string|Htmlable
    {
        return 'Buat Pengajuan Barang';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Buat Pengajuan')
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
}
