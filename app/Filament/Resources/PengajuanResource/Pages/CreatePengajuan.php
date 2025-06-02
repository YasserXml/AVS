<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use App\Models\Barang;
use App\Models\Pengajuan;
use App\Services\AdminNotificationService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            // Generate batch_id unik untuk pengajuan bersamaan
            $batchId = 'PGJ-' . now()->format('YmdHis') . '-' . Str::random(6);

            if (!isset($data['status_barang']) || $data['status_barang'] === null) {
                $data['status_barang'] = 'oprasional_kantor';
            }

            $baseData = [
                'batch_id' => $batchId, // batch_id untuk mengelompokkan
                'user_id' => $data['user_id'],
                'tanggal_pengajuan' => $data['tanggal_pengajuan'],
                'status_barang' => $data['status_barang'],
                'nama_project' => $data['nama_project'] ?? null,
                'keterangan' => $data['keterangan'] ?? null,
                'status' => 'pending',
            ];

            $createdRecords = [];

            if (isset($data['detail_pengajuan']) && count($data['detail_pengajuan']) > 0) {
                foreach ($data['detail_pengajuan'] as $detailItem) {
                    if (!isset($detailItem['nama_barang']) || empty($detailItem['nama_barang'])) {
                        continue;
                    }

                    // Menggunakan field nama_barang dan detail_barang yang baru
                    $pengajuanData = array_merge($baseData, [
                        'barang_id' => null, // Nullable sekarang
                        'kategoris_id' => null, // Nullable sekarang
                        'nama_barang' => $detailItem['nama_barang'], // Field baru untuk nama barang
                        'detail_barang' => $detailItem['detail_barang'] ?? null, // Field baru untuk detail barang
                        'Jumlah_barang_diajukan' => $detailItem['Jumlah_barang_diajukan'],
                    ]);

                    // Keterangan tetap menggunakan field keterangan yang sudah ada
                    // Detail barang sekarang disimpan di field terpisah
                    $pengajuanData['keterangan'] = $baseData['keterangan'];
                    $createdRecords[] = static::getModel()::create($pengajuanData);
                }

                // Kirim notifikasi
                if (!empty($createdRecords)) {
                    try {
                        $pengaju = filament()->auth()->user();
                        $pengajuanCollection = collect($createdRecords);
                        AdminNotificationService::sendPengajuanNotification($pengajuanCollection, $pengaju);
                    } catch (\Exception $e) {
                        Log::error('Gagal mengirim notifikasi email pengajuan: ' . $e->getMessage());
                    }
                }

                Notification::make()
                    ->title('Pengajuan barang berhasil dibuat')
                    ->body('Sebanyak ' . count($createdRecords) . ' barang berhasil diajukan')
                    ->success()
                    ->send();
            }

            DB::commit();
            return $createdRecords[0] ?? static::getModel()::latest()->first();
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->danger()
                ->title('Terjadi kesalahan')
                ->body('Gagal membuat pengajuan: ' . $e->getMessage())
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
