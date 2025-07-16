<?php

namespace App\Models;

use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Pengajuanproject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nomor_pengajuan',
        'batch_id',
        'user_id',
        'tanggal_pengajuan',
        'tanggal_dibutuhkan',
        'detail_barang',
        'uploaded_files',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'reject_reason',
        'received_by',
        'received_by_name',
        'status_history',
        'catatan',
        'project_id',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'tanggal_dibutuhkan' => 'date',
        'detail_barang' => 'array',
        'uploaded_files' => 'array',
        'status_history' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function nameproject()
    {
        return $this->belongsTo(Nameproject::class, 'project_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function getProjectNameAttribute(): string
    {
        return $this->nameproject?->nama_project ?? 'Project tidak ditemukan';
    }

    public function getProjectManagerAttribute(): string
    {
        return $this->nameproject?->user?->name ?? 'PM tidak ditemukan';
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public const STATUS_PENGAJUAN_TERKIRIM = 'pengajuan_terkirim';
    public const STATUS_PENDING_PM_REVIEW = 'pending_pm_review';
    public const STATUS_DISETUJUI_PM_DIKIRIM_KE_PENGADAAN = 'disetujui_pm_dikirim_ke_pengadaan';
    public const STATUS_DITOLAK_PM = 'ditolak_pm';
    public const STATUS_DISETUJUI_PENGADAAN = 'disetujui_pengadaan';
    public const STATUS_DITOLAK_PENGADAAN = 'ditolak_pengadaan';
    public const STATUS_PENGAJUAN_DIKIRIM_KE_DIREKSI = 'pengajuan_dikirim_ke_direksi';
    public const STATUS_APPROVED_BY_DIREKSI = 'approved_by_direksi';
    public const STATUS_REJECT_DIREKSI = 'reject_direksi';
    public const STATUS_PENGAJUAN_DIKIRIM_KE_KEUANGAN = 'pengajuan_dikirim_ke_keuangan';
    public const STATUS_PENDING_KEUANGAN = 'pending_keuangan';
    public const STATUS_PROCESS_KEUANGAN = 'process_keuangan';
    public const STATUS_EXECUTE_KEUANGAN = 'execute_keuangan';
    public const STATUS_PENGAJUAN_DIKIRIM_KE_PENGADAAN_FINAL = 'pengajuan_dikirim_ke_pengadaan_final';
    public const STATUS_PENGAJUAN_DIKIRIM_KE_ADMIN = 'pengajuan_dikirim_ke_admin';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY_PICKUP = 'ready_pickup';
    public const STATUS_COMPLETED = 'completed';

    // Konstanta untuk rejection
    public const REJECTED_BY_PM = 'pm';
    public const REJECTED_BY_PENGADAAN = 'pengadaan';
    public const REJECTED_BY_DIREKSI = 'direksi';


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->nomor_pengajuan)) {
                $model->nomor_pengajuan = $model->generateNomorPengajuan();
            }
        });
    }

    public function generateNomorPengajuan(): string
    {
        return DB::transaction(function () {
            $prefix = 'PO-';
            $date = Carbon::now()->format('Ymd');
            $lockKey = "pengajuan_nomor_lock_{$date}";

            DB::selectOne("SELECT pg_advisory_xact_lock(hashtext(?))", [$lockKey]);

            // Cari nomor terakhir
            $lastRecord = self::where('nomor_pengajuan', 'like', $prefix . $date . '-%')
                ->orderByRaw("CAST(SUBSTRING(nomor_pengajuan FROM '-(\\d+)$') AS INTEGER) DESC")
                ->first();

            if ($lastRecord) {
                preg_match('/(\d+)$/', $lastRecord->nomor_pengajuan, $matches);
                $lastSequence = isset($matches[1]) ? (int) $matches[1] : 0;
                $sequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $sequence = '0001';
            }

            $nomorPengajuan = $prefix . $date . '-' . $sequence;

            // Pastikan nomor belum ada
            if (self::where('nomor_pengajuan', $nomorPengajuan)->exists()) {
                return $this->generateFallbackNomorPengajuan();
            }

            return $nomorPengajuan;
        });
    }

    public function generateFallbackNomorPengajuan(): string
    {
        $prefix = 'PO-';
        $date = Carbon::now()->format('Ymd');
        $uuid = strtoupper(substr(str_replace('-', '', Str::uuid()), 0, 8));

        return $prefix . $date . '-' . $uuid;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENGAJUAN_TERKIRIM => 'Pengajuan Terkirim',
            self::STATUS_PENDING_PM_REVIEW => 'Menunggu Review PM',
            self::STATUS_DISETUJUI_PM_DIKIRIM_KE_PENGADAAN => 'Disetujui PM - Dikirim ke Pengadaan',
            self::STATUS_DITOLAK_PM => 'Ditolak PM',
            self::STATUS_DISETUJUI_PENGADAAN => 'Disetujui Pengadaan',
            self::STATUS_DITOLAK_PENGADAAN => 'Ditolak Pengadaan',
            self::STATUS_PENGAJUAN_DIKIRIM_KE_DIREKSI => 'Dikirim ke Direksi',
            self::STATUS_APPROVED_BY_DIREKSI => 'Disetujui Direksi',
            self::STATUS_REJECT_DIREKSI => 'Ditolak Direksi',
            self::STATUS_PENGAJUAN_DIKIRIM_KE_KEUANGAN => 'Dikirim ke Keuangan',
            self::STATUS_PENDING_KEUANGAN => 'Menunggu Keuangan',
            self::STATUS_PROCESS_KEUANGAN => 'Diproses Keuangan',
            self::STATUS_EXECUTE_KEUANGAN => 'Eksekusi Keuangan',
            self::STATUS_PENGAJUAN_DIKIRIM_KE_PENGADAAN_FINAL => 'Dikirim ke Pengadaan Final',
            self::STATUS_PENGAJUAN_DIKIRIM_KE_ADMIN => 'Dikirim ke Admin',
            self::STATUS_PROCESSING => 'Sedang Diproses',
            self::STATUS_READY_PICKUP => 'Siap Diambil',
            self::STATUS_COMPLETED => 'Selesai',
            default => 'Status Tidak Diketahui',
        };
    }

    // Accessor untuk warna status
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENGAJUAN_TERKIRIM => 'gray',
            self::STATUS_PENDING_PM_REVIEW => 'warning',
            self::STATUS_DISETUJUI_PM_DIKIRIM_KE_PENGADAAN => 'info',
            self::STATUS_DITOLAK_PM => 'danger',
            self::STATUS_DISETUJUI_PENGADAAN => 'success',
            self::STATUS_DITOLAK_PENGADAAN => 'danger',
            self::STATUS_PENGAJUAN_DIKIRIM_KE_DIREKSI => 'info',
            self::STATUS_APPROVED_BY_DIREKSI => 'success',
            self::STATUS_REJECT_DIREKSI => 'danger',
            self::STATUS_PENGAJUAN_DIKIRIM_KE_KEUANGAN => 'info',
            self::STATUS_PENDING_KEUANGAN => 'warning',
            self::STATUS_PROCESS_KEUANGAN => 'warning',
            self::STATUS_EXECUTE_KEUANGAN => 'primary',
            self::STATUS_PENGAJUAN_DIKIRIM_KE_PENGADAAN_FINAL => 'info',
            self::STATUS_PENGAJUAN_DIKIRIM_KE_ADMIN => 'info',
            self::STATUS_PROCESSING => 'warning',
            self::STATUS_READY_PICKUP => 'primary',
            self::STATUS_COMPLETED => 'success',
            default => 'gray',
        };
    }

    // Accessor untuk total item
    public function getTotalItemAttribute(): int
    {
        if (!$this->detail_barang) {
            return 0;
        }

        return collect($this->detail_barang)->sum('jumlah_barang_diajukan');
    }

    // Accessor untuk total jenis barang
    public function getTotalJenisBarangAttribute(): int
    {
        if (!$this->detail_barang) {
            return 0;
        }

        return count($this->detail_barang);
    }

    // Scope untuk filter berdasarkan user
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope untuk filter berdasarkan rentang tanggal
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_pengajuan', [$startDate, $endDate]);
    }

    // Scope untuk pengajuan yang pending approval
    public function scopePendingApproval($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENGAJUAN_TERKIRIM,
            self::STATUS_PENDING_PM_REVIEW,
            self::STATUS_DISETUJUI_PM_DIKIRIM_KE_PENGADAAN,
            self::STATUS_PENGAJUAN_DIKIRIM_KE_DIREKSI,
            self::STATUS_PENGAJUAN_DIKIRIM_KE_KEUANGAN,
            self::STATUS_PENDING_KEUANGAN,
            self::STATUS_PROCESS_KEUANGAN,
            self::STATUS_EXECUTE_KEUANGAN,
            self::STATUS_PENGAJUAN_DIKIRIM_KE_PENGADAAN_FINAL,
            self::STATUS_PENGAJUAN_DIKIRIM_KE_ADMIN,
        ]);
    }

    // Scope untuk pengajuan yang disetujui
    public function scopeApproved($query)
    {
        return $query->whereIn('status', [
            self::STATUS_DISETUJUI_PENGADAAN,
            self::STATUS_APPROVED_BY_DIREKSI,
            self::STATUS_PROCESSING,
            self::STATUS_READY_PICKUP,
            self::STATUS_COMPLETED,
        ]);
    }

    // Scope untuk pengajuan yang ditolak
    public function scopeRejected($query)
    {
        return $query->whereIn('status', [
            self::STATUS_DITOLAK_PM,
            self::STATUS_DITOLAK_PENGADAAN,
            self::STATUS_REJECT_DIREKSI,
        ]);
    }

    // Cek apakah status bisa diupdate
    public function canUpdateStatus(): bool
    {
        return $this->status !== self::STATUS_COMPLETED;
    }

    // Cek apakah pengajuan bisa dibatalkan
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENGAJUAN_TERKIRIM,
            self::STATUS_PENDING_PM_REVIEW,
            self::STATUS_DISETUJUI_PM_DIKIRIM_KE_PENGADAAN,
            self::STATUS_PENGAJUAN_DIKIRIM_KE_DIREKSI,
        ]);
    }

    public function getNextPossibleStatuses(): array
    {
        return match ($this->status) {
            self::STATUS_PENGAJUAN_TERKIRIM => [
                self::STATUS_PENDING_PM_REVIEW => 'Kirim ke PM untuk Review',
            ],
            self::STATUS_PENDING_PM_REVIEW => [
                self::STATUS_DISETUJUI_PM_DIKIRIM_KE_PENGADAAN => 'Setujui dan Kirim ke Pengadaan',
                self::STATUS_DITOLAK_PM => 'Tolak Pengajuan',
            ],
            self::STATUS_DISETUJUI_PM_DIKIRIM_KE_PENGADAAN => [
                self::STATUS_DISETUJUI_PENGADAAN => 'Setujui Pengadaan',
                self::STATUS_PENGAJUAN_DIKIRIM_KE_DIREKSI => 'Kirim ke Direksi',
                self::STATUS_DITOLAK_PENGADAAN => 'Tolak Pengadaan',
            ],
            self::STATUS_DISETUJUI_PENGADAAN => [
                self::STATUS_PROCESSING => 'Mulai Proses',
            ],
            self::STATUS_PENGAJUAN_DIKIRIM_KE_DIREKSI => [
                self::STATUS_APPROVED_BY_DIREKSI => 'Setujui Direksi',
                self::STATUS_REJECT_DIREKSI => 'Tolak Direksi',
            ],
            self::STATUS_APPROVED_BY_DIREKSI => [
                self::STATUS_PENGAJUAN_DIKIRIM_KE_KEUANGAN => 'Kirim ke Keuangan',
            ],
            self::STATUS_PENGAJUAN_DIKIRIM_KE_KEUANGAN => [
                self::STATUS_PENDING_KEUANGAN => 'Pending Keuangan',
            ],
            self::STATUS_PENDING_KEUANGAN => [
                self::STATUS_PROCESS_KEUANGAN => 'Proses Keuangan',
            ],
            self::STATUS_PROCESS_KEUANGAN => [
                self::STATUS_EXECUTE_KEUANGAN => 'Eksekusi Keuangan',
            ],
            self::STATUS_EXECUTE_KEUANGAN => [
                self::STATUS_PENGAJUAN_DIKIRIM_KE_PENGADAAN_FINAL => 'Kirim ke Pengadaan Final',
            ],
            self::STATUS_PENGAJUAN_DIKIRIM_KE_PENGADAAN_FINAL => [
                self::STATUS_PROCESSING => 'Mulai Proses',
            ],
            self::STATUS_PROCESSING => [
                self::STATUS_READY_PICKUP => 'Siap Diambil',
            ],
            self::STATUS_READY_PICKUP => [
                self::STATUS_COMPLETED => 'Selesai',
            ],
            default => [],
        };
    }

    public function getProgressPercentage(): int
    {
        return match ($this->status) {
            self::STATUS_PENGAJUAN_TERKIRIM => 5,
            self::STATUS_PENDING_PM_REVIEW => 10,
            self::STATUS_DISETUJUI_PM_DIKIRIM_KE_PENGADAAN => 15,
            self::STATUS_DISETUJUI_PENGADAAN => 25,
            self::STATUS_PENGAJUAN_DIKIRIM_KE_DIREKSI => 30,
            self::STATUS_APPROVED_BY_DIREKSI => 40,
            self::STATUS_PENGAJUAN_DIKIRIM_KE_KEUANGAN => 45,
            self::STATUS_PENDING_KEUANGAN => 50,
            self::STATUS_PROCESS_KEUANGAN => 55,
            self::STATUS_EXECUTE_KEUANGAN => 60,
            self::STATUS_PENGAJUAN_DIKIRIM_KE_PENGADAAN_FINAL => 70,
            self::STATUS_PENGAJUAN_DIKIRIM_KE_ADMIN => 75,
            self::STATUS_PROCESSING => 85,
            self::STATUS_READY_PICKUP => 95,
            self::STATUS_COMPLETED => 100,
            self::STATUS_DITOLAK_PM => 10,
            self::STATUS_DITOLAK_PENGADAAN => 15,
            self::STATUS_REJECT_DIREKSI => 30,
            default => 0,
        };
    }

    public function addStatusHistory(string $status, int $userId, string $note = null): void
    {
        $history = $this->status_history ?? [];
        $user = User::find($userId);

        $history[] = [
            'status' => $status,
            'user_id' => $userId,
            'user_name' => $user ? $user->name : 'Unknown User',
            'note' => $note,
            'created_at' => now()->toISOString(),
        ];

        $this->update(['status_history' => $history]);
    }
}
