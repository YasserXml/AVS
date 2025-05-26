<?php

namespace App\Services;

use App\Models\Pengajuan;
use App\Models\Barang;
use App\Models\Barangkeluar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class PengajuanApprovalService
{
    /**
     * Approve grup pengajuan bersamaan
     */
    public function approveBatch(Pengajuan $record, array $data): array
    {
        try {
            // Ambil semua pengajuan dalam grup yang sama dan masih pending
            $grupPengajuans = Pengajuan::where('batch_id', $record->batch_id)
                ->where('status', 'pending')
                ->with('barang')
                ->get();

            $approvedCount = 0;
            $failedItems = [];

            DB::transaction(function () use ($grupPengajuans, $data, &$approvedCount, &$failedItems) {
                foreach ($grupPengajuans as $pengajuan) {
                    $result = $this->approveIndividualPengajuan($pengajuan, $data);

                    if ($result['success']) {
                        $approvedCount++;
                    } else {
                        $failedItems[] = $result['error'];
                    }
                }
            });

            // Kirim notifikasi hasil
            $this->sendApprovalNotifications($approvedCount, $failedItems, $record, $grupPengajuans->first());

            return [
                'success' => true,
                'approved_count' => $approvedCount,
                'failed_items' => $failedItems
            ];
        } catch (\Exception $e) {
            Log::error('Error saat approve grup pengajuan: ' . $e->getMessage());

            Notification::make()
                ->title('Gagal Menyetujui Pengajuan')
                ->body('Terjadi kesalahan saat memproses grup pengajuan.')
                ->danger()
                ->send();

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Approve pengajuan individual
     */
    private function approveIndividualPengajuan(Pengajuan $pengajuan, array $data): array
    {
        try {
            // Periksa stok tersedia
            $barang = Barang::find($pengajuan->barang_id);
            if ($barang->jumlah_barang < $pengajuan->Jumlah_barang_diajukan) {
                return [
                    'success' => false,
                    'error' => "{$barang->nama_barang} (stok tidak mencukupi: tersedia {$barang->jumlah_barang}, diminta {$pengajuan->Jumlah_barang_diajukan})"
                ];
            }

            // Set tanggal keluar
            $tanggalKeluar = $data['tanggal_keluar'] ?? now()->format('Y-m-d');

            // Catat barang keluar
            $barangKeluar = Barangkeluar::create([
                'barang_id' => $pengajuan->barang_id,
                'pengajuan_id' => $pengajuan->id,
                'user_id' => Auth::id(),
                'jumlah_barang_keluar' => $pengajuan->Jumlah_barang_diajukan,
                'tanggal_keluar_barang' => $tanggalKeluar,
                'keterangan' => $data['keterangan_barang_keluar'],
                'status' => $pengajuan->status_barang,
            ]);

            // Update pengajuan
            $pengajuan->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Kurangi stok barang
            $barang->decrement('jumlah_barang', $pengajuan->Jumlah_barang_diajukan);

            Log::info("Barang keluar berhasil dibuat untuk pengajuan ID: {$pengajuan->id}, tanggal: {$tanggalKeluar}");

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Error saat approve item individual: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => "{$pengajuan->barang->nama_barang} (error: {$e->getMessage()})"
            ];
        }
    }

    /**
     * Reject grup pengajuan bersamaan
     */
    public function rejectBatch(Pengajuan $record, array $data): array
    {
        try {
            // Ambil semua pengajuan dalam grup yang sama dan masih pending
            $grupPengajuans = Pengajuan::where('batch_id', $record->batch_id)
                ->where('status', 'pending')
                ->with(['user', 'barang'])
                ->get();

            $rejectedCount = 0;

            DB::transaction(function () use ($grupPengajuans, $data, &$rejectedCount) {
                foreach ($grupPengajuans as $pengajuan) {
                    $pengajuan->update([
                        'status' => 'rejected',
                        'reject_by' => Auth::id(),
                        'reject_reason' => $data['reject_reason'],
                        'rejected_at' => now(),
                    ]);
                    $rejectedCount++;
                }
            });

            // Generate nama grup yang lebih deskriptif untuk notifikasi
            $firstPengajuan = $grupPengajuans->first();
            $grupName = $this->generateGrupName($firstPengajuan);

            Notification::make()
                ->title('Pengajuan Berhasil Ditolak')
                ->body("Berhasil menolak {$rejectedCount} pengajuan barang dari {$grupName}")
                ->success()
                ->send();

            return [
                'success' => true,
                'rejected_count' => $rejectedCount
            ];
        } catch (\Exception $e) {
            Log::error('Error saat reject grup pengajuan: ' . $e->getMessage());

            Notification::make()
                ->title('Gagal Menolak Pengajuan')
                ->body('Terjadi kesalahan saat memproses grup pengajuan.')
                ->danger()
                ->send();

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate deskripsi modal untuk approval
     */
    public function generateApprovalModalDescription(Pengajuan $record): string
    {
        $grupPengajuans = Pengajuan::where('batch_id', $record->batch_id)
            ->where('status', 'pending')
            ->with(['barang', 'user'])
            ->get();

        $totalItems = $grupPengajuans->count();

        if ($totalItems == 1) {
            // Pengajuan tunggal
            return "Anda akan menyetujui pengajuan barang:\n\n• {$record->barang->nama_barang} ({$record->Jumlah_barang_diajukan} unit)";
        }

        // Pengajuan bersamaan
        $firstPengajuan = $grupPengajuans->first();
        $grupName = $this->generateGrupName($firstPengajuan);

        $itemList = $grupPengajuans->map(function ($item) {
            return "• {$item->barang->nama_barang} ({$item->Jumlah_barang_diajukan} unit)";
        })->join("\n");

        return "Anda akan menyetujui {$totalItems} pengajuan barang sekaligus dari {$grupName}:\n\n{$itemList}\n\n💡 Semua item dalam grup pengajuan ini akan diproses bersamaan.";
    }

    /**
     * Generate deskripsi modal untuk rejection
     */
    public function generateRejectionModalDescription(Pengajuan $record): string
    {
        $grupPengajuans = Pengajuan::where('batch_id', $record->batch_id)
            ->where('status', 'pending')
            ->with(['barang', 'user'])
            ->get();

        $totalItems = $grupPengajuans->count();

        if ($totalItems == 1) {
            // Pengajuan tunggal
            return "Anda akan menolak pengajuan barang:\n\n• {$record->barang->nama_barang} ({$record->Jumlah_barang_diajukan} unit)";
        }

        // Pengajuan bersamaan
        $firstPengajuan = $grupPengajuans->first();
        $grupName = $this->generateGrupName($firstPengajuan);

        $itemList = $grupPengajuans->map(function ($item) {
            return "• {$item->barang->nama_barang} ({$item->Jumlah_barang_diajukan} unit)";
        })->join("\n");

        return "Anda akan menolak {$totalItems} pengajuan barang sekaligus dari {$grupName}:\n\n{$itemList}\n\n⚠️ Semua item dalam grup pengajuan ini akan ditolak dengan alasan yang sama.";
    }

    /**
     * Generate nama grup yang lebih deskriptif
     */
    private function generateGrupName(Pengajuan $pengajuan): string
    {
        $tanggal = Carbon::parse($pengajuan->tanggal_pengajuan)->format('d M Y');
        $pemohon = $pengajuan->user->name ?? 'Unknown';

        return "pengajuan {$pemohon} tanggal {$tanggal}";
    }

    /**
     * Kirim notifikasi hasil approval
     */
    private function sendApprovalNotifications(int $approvedCount, array $failedItems, Pengajuan $record, ?Pengajuan $firstPengajuan): void
    {
        if ($approvedCount > 0) {
            $grupName = $firstPengajuan ? $this->generateGrupName($firstPengajuan) : 'grup pengajuan';

            if ($approvedCount == 1) {
                Notification::make()
                    ->title('Pengajuan Berhasil Disetujui')
                    ->body("Berhasil menyetujui pengajuan barang {$record->barang->nama_barang}")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Pengajuan Berhasil Disetujui')
                    ->body("Berhasil menyetujui {$approvedCount} pengajuan barang dari {$grupName}")
                    ->success()
                    ->send();
            }
        }

        if (!empty($failedItems)) {
            Notification::make()
                ->title('Beberapa Item Gagal Disetujui')
                ->body("Item yang gagal disetujui:\n" . implode("\n", $failedItems))
                ->warning()
                ->persistent()
                ->send();
        }
    }

    /**
     * Cek apakah pengajuan ini bagian dari grup pengajuan bersamaan
     */
    public function isGroupSubmission(Pengajuan $pengajuan): bool
    {
        if (empty($pengajuan->batch_id)) {
            return false;
        }

        $grupCount = Pengajuan::where('batch_id', $pengajuan->batch_id)->count();
        return $grupCount > 1;
    }

    /**
     * Dapatkan semua pengajuan dalam grup yang sama
     */
    public function getGroupMembers(Pengajuan $pengajuan): Collection
    {
        return Pengajuan::where('batch_id', $pengajuan->batch_id)
            ->with(['barang', 'user'])
            ->get();
    }
}
