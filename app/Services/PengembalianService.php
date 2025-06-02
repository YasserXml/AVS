<?php
namespace App\Services;

use App\Models\Pengajuan;
use App\Models\Pengembalian;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengembalianService
{
    /**
     * Buat pengajuan pengembalian baru
     */
    public function buatPengembalian($data)
    {
        try {
            DB::beginTransaction();

            // Validasi pengajuan
            $pengajuan = Pengajuan::findOrFail($data['pengajuan_id']);
            
            if (!$pengajuan->bisaDikembalikan()) {
                throw new Exception('Pengajuan tidak dapat dikembalikan');
            }

            // Cek sisa yang bisa dikembalikan
            $sisaBisaDikembalikan = $pengajuan->sisaBisaDikembalikan();
            
            if ($data['jumlah_dikembalikan'] > $sisaBisaDikembalikan) {
                throw new Exception("Jumlah pengembalian melebihi sisa yang dapat dikembalikan ({$sisaBisaDikembalikan})");
            }

            // Buat pengembalian
            $pengembalian = Pengembalian::create([
                'pengajuan_id' => $data['pengajuan_id'],
                'barang_id' => $pengajuan->barang_id,
                'user_id' => Auth::id(),
                'barang_keluar_id' => $data['barang_keluar_id'] ?? null,
                'jumlah_dikembalikan' => $data['jumlah_dikembalikan'],
                'tanggal_pengembalian' => $data['tanggal_pengembalian'],
                'kondisi' => $data['kondisi'] ?? 'baik',
                'keterangan' => $data['keterangan'] ?? null,
                'status' => 'pending',
            ]);

            DB::commit();

            return [
                'success' => true,
                'data' => $pengembalian,
                'message' => 'Pengajuan pengembalian berhasil dibuat'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Approve pengembalian
     */
    public function approvePengembalian($pengembalianId, $approvedBy = null)
    {
        try {
            DB::beginTransaction();

            $pengembalian = Pengembalian::findOrFail($pengembalianId);
            
            if ($pengembalian->status !== 'pending') {
                throw new Exception('Pengembalian sudah diproses sebelumnya');
            }

            // Update status pengembalian
            $pengembalian->update([
                'status' => 'approved',
                'approved_by' => $approvedBy ?? Auth::id(),
                'approved_at' => now(),
            ]);

            // Tambah stok barang kembali berdasarkan kondisi
            $barang = $pengembalian->barang;
            
            if ($pengembalian->kondisi === 'baik') {
                // Jika kondisi baik, tambah ke stok
                $barang->tambahStok($pengembalian->jumlah_dikembalikan);
            } else {
                // Jika rusak/hilang, tidak ditambah ke stok tapi tetap tercatat sebagai dikembalikan
                // Bisa ditambah logic khusus untuk barang rusak/hilang
            }

            DB::commit();

            return [
                'success' => true,
                'data' => $pengembalian->fresh(),
                'message' => 'Pengembalian berhasil disetujui'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Reject pengembalian
     */
    public function rejectPengembalian($pengembalianId, $reason, $rejectedBy = null)
    {
        try {
            DB::beginTransaction();

            $pengembalian = Pengembalian::findOrFail($pengembalianId);
            
            if ($pengembalian->status !== 'pending') {
                throw new Exception('Pengembalian sudah diproses sebelumnya');
            }

            // Update status pengembalian
            $pengembalian->update([
                'status' => 'rejected',
                'rejected_by' => $rejectedBy ?? Auth::id(),
                'reject_reason' => $reason,
                'rejected_at' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'data' => $pengembalian->fresh(),
                'message' => 'Pengembalian ditolak'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get daftar pengajuan yang bisa dikembalikan untuk user
     */
    public function getPengajuanBisaDikembalikan($userId = null)
    {
        $userId = $userId ?? Auth::id();

        return Pengajuan::with(['barang', 'pengembalians'])
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->whereHas('barangKeluar');
    }

    /**
     * Get riwayat pengembalian
     */
    public function getRiwayatPengembalian($userId = null)
    {
        $query = Pengembalian::with([
            'pengajuan.barang',
            'barang',
            'user',
            'approvedBy',
            'rejectedBy'
        ]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->latest()->get();
    }

    /**
     * Get detail pengembalian
     */
    public function getDetailPengembalian($pengembalianId)
    {
        return Pengembalian::with([
            'pengajuan.barang',
            'barang',
            'user',
            'barangKeluar',
            'approvedBy',
            'rejectedBy'
        ])->findOrFail($pengembalianId);
    }

    /**
     * Bulk approve pengembalian
     */
    public function bulkApprovePengembalian($pengembalianIds, $approvedBy = null)
    {
        try {
            DB::beginTransaction();

            $results = [];
            
            foreach ($pengembalianIds as $id) {
                $result = $this->approvePengembalian($id, $approvedBy);
                $results[] = $result;
                
                if (!$result['success']) {
                    throw new Exception("Gagal approve pengembalian ID {$id}: " . $result['message']);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'data' => $results,
                'message' => 'Semua pengembalian berhasil disetujui'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}