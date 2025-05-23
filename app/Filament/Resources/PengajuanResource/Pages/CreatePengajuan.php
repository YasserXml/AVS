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
                'batch_id' => $batchId, //batch_id untuk mengelompokkan
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
                    if (!isset($detailItem['barang_id']) || empty($detailItem['barang_id'])) {
                        continue;
                    }

                    $barang = Barang::findOrFail($detailItem['barang_id']);

                    $pengajuanData = array_merge($baseData, [
                        'barang_id' => $detailItem['barang_id'],
                        'kategoris_id' => $detailItem['kategoris_id'] ?? $barang->kategori_id,
                        'Jumlah_barang_diajukan' => $detailItem['Jumlah_barang_diajukan'],
                    ]);

                    $finalKeterangan = $pengajuanData['keterangan'] ?? '';

                    if (!empty($detailItem['catatan_barang'])) {
                        if (!empty($finalKeterangan)) {
                            $finalKeterangan .= "\n\n--- Catatan Barang: " . $barang->nama_barang . " ---\n";
                            $finalKeterangan .= $detailItem['catatan_barang'];
                        } else {
                            $finalKeterangan = "Catatan Barang: " . $barang->nama_barang . "\n" . $detailItem['catatan_barang'];
                        }
                    }

                    $pengajuanData['keterangan'] = $finalKeterangan;
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
                    ->body('Sebanyak ' . count($createdRecords))
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
