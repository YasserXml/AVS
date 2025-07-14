<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class Pengajuanoprasional extends Model
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

    public const STATUS_PENGAJUAN_TERKIRIM = 'pengajuan_terkirim';
    public const STATUS_PENDING_ADMIN_REVIEW = 'pending_admin_review';
    public const STATUS_DIAJUKAN_KE_SUPERADMIN = 'diajukan_ke_superadmin';
    public const STATUS_SUPERADMIN_APPROVED = 'superadmin_approved';
    public const STATUS_SUPERADMIN_REJECTED = 'superadmin_rejected';
    public const STATUS_PENGAJUAN_DIKIRIM_KE_DIREKSI = 'pengajuan_dikirim_ke_direksi';
    public const STATUS_APPROVED_BY_DIREKSI = 'approved_by_direksi';
    public const STATUS_APPROVED_AT_DIREKSI = 'approved_at_direksi';
    public const STATUS_REJECT_DIREKSI = 'reject_direksi';
    public const STATUS_PENGAJUAN_DIKIRIM_KE_KEUANGAN = 'pengajuan_dikirim_ke_keuangan';
    public const STATUS_PENDING_KEUANGAN = 'pending_keuangan';
    public const STATUS_PROCESS_KEUANGAN = 'process_keuangan';
    public const STATUS_EXECUTE_KEUANGAN = 'execute_keuangan';
    public const STATUS_EXECUTED_BY_KEUANGAN = 'executed_by_keuangan';
    public const STATUS_EXECUTED_AT_KEUANGAN = 'executed_at_keuangan';
    public const STATUS_PENGAJUAN_DIKIRIM_KE_PENGADAAN = 'pengajuan_dikirim_ke_pengadaan';
    public const STATUS_PENGAJUAN_DIKIRIM_KE_ADMIN = 'pengajuan_dikirim_ke_admin';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY_PICKUP = 'ready_pickup';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const REJECTED_BY_ADMIN = 'admin';
    public const REJECTED_BY_SUPERADMIN = 'superadmin';

    public function user()
    {
        return $this->belongsTo(User::class);
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->nomor_pengajuan)) {
                $model->nomor_pengajuan = $model->generateNomorPengajuan();
            }
        });
    }

    public function addStatusHistory(string $status, int $userId, string $note): void
    {
        $history = $this->status_history ?? [];

        $history[] = [
            'status' => $status,
            'user_id' => $userId,
            'note' => $note,
            'created_at' => now()->toISOString(),
        ];

        $this->update(['status_history' => $history]);
    }

    public function generateNomorPengajuan(): string
    {
        return DB::transaction(function () {
            $prefix = 'PO-';
            $date = Carbon::now()->format('Ymd');
            $lockKey = "pengajuan_nomor_lock_{$date}";

            // Gunakan advisory lock untuk mencegah race condition
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
            self::STATUS_PENDING_ADMIN_REVIEW => 'Menunggu Review Admin',
            self::STATUS_DIAJUKAN_KE_SUPERADMIN => 'Diajukan ke Pengadaan',
            self::STATUS_SUPERADMIN_APPROVED => 'Disetujui Pengadaan',
            self::STATUS_SUPERADMIN_REJECTED => 'Ditolak Pengadaan',
            self::STATUS_PENGAJUAN_DIKIRIM_KE_DIREKSI => 'Dikirim ke Direksi',
            self::STATUS_APPROVED_BY_DIREKSI => 'Disetujui Direksi',
            self::STATUS_APPROVED_AT_DIREKSI => 'Disetujui pada Direksi',
            self::STATUS_REJECT_DIREKSI => 'Ditolak Direksi',
            self::STATUS_PENGAJUAN_DIKIRIM_KE_KEUANGAN => 'Dikirim ke Keuangan',
            self::STATUS_PENDING_KEUANGAN => 'Menunggu Keuangan',
            self::STATUS_PROCESS_KEUANGAN => 'Diproses Keuangan',
            self::STATUS_EXECUTE_KEUANGAN => 'Eksekusi Keuangan',
            self::STATUS_EXECUTED_BY_KEUANGAN => 'Dieksekusi Keuangan',
            self::STATUS_EXECUTED_AT_KEUANGAN => 'Dieksekusi pada Keuangan',
            self::STATUS_PENGAJUAN_DIKIRIM_KE_PENGADAAN => 'Dikirim ke Pengadaan',
            self::STATUS_PENGAJUAN_DIKIRIM_KE_ADMIN => 'Dikirim ke Admin',
            self::STATUS_PROCESSING => 'Sedang Diproses',
            self::STATUS_READY_PICKUP => 'Siap Diambil',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => 'Status Tidak Diketahui',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENGAJUAN_TERKIRIM => 'gray',
            self::STATUS_PENDING_ADMIN_REVIEW => 'warning',
            self::STATUS_DIAJUKAN_KE_SUPERADMIN => 'warning',
            self::STATUS_SUPERADMIN_APPROVED => 'success',
            self::STATUS_SUPERADMIN_REJECTED => 'danger',
            self::STATUS_PENGAJUAN_DIKIRIM_KE_DIREKSI => 'info',
            self::STATUS_APPROVED_BY_DIREKSI => 'success',
            self::STATUS_APPROVED_AT_DIREKSI => 'success',
            self::STATUS_REJECT_DIREKSI => 'danger',
            self::STATUS_PENGAJUAN_DIKIRIM_KE_KEUANGAN => 'info',
            self::STATUS_PENDING_KEUANGAN => 'warning',
            self::STATUS_PROCESS_KEUANGAN => 'warning',
            self::STATUS_EXECUTE_KEUANGAN => 'primary',
            self::STATUS_EXECUTED_BY_KEUANGAN => 'success',
            self::STATUS_EXECUTED_AT_KEUANGAN => 'success',
            self::STATUS_PENGAJUAN_DIKIRIM_KE_PENGADAAN => 'info',
            self::STATUS_PENGAJUAN_DIKIRIM_KE_ADMIN => 'info',
            self::STATUS_PROCESSING => 'warning',
            self::STATUS_READY_PICKUP => 'primary',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            default => 'gray',
        };
    }

    public function getTotalItemAttribute(): int
    {
        if (!$this->detail_barang) {
            return 0;
        }

        return collect($this->detail_barang)->sum('jumlah_barang_diajukan'); 
    }

    public function getTotalJenisBarangAttribute(): int
    {
        if (!$this->detail_barang) {
            return 0;
        }

        return count($this->detail_barang);
    }
 
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_pengajuan', [$startDate, $endDate]);
    }

    public function scopePendingApproval($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENGAJUAN_TERKIRIM,
            self::STATUS_PENDING_ADMIN_REVIEW,
            self::STATUS_DIAJUKAN_KE_SUPERADMIN,
            self::STATUS_PENGAJUAN_DIKIRIM_KE_DIREKSI,
            self::STATUS_PENGAJUAN_DIKIRIM_KE_KEUANGAN,
            self::STATUS_PENDING_KEUANGAN,
            self::STATUS_PROCESS_KEUANGAN,
            self::STATUS_EXECUTE_KEUANGAN,
            self::STATUS_PENGAJUAN_DIKIRIM_KE_PENGADAAN,
            self::STATUS_PENGAJUAN_DIKIRIM_KE_ADMIN,
        ]);
    }

    public function scopeApproved($query)
    {
        return $query->whereIn('status', [
            self::STATUS_SUPERADMIN_APPROVED,
            self::STATUS_APPROVED_BY_DIREKSI,
            self::STATUS_APPROVED_AT_DIREKSI,
            self::STATUS_EXECUTED_BY_KEUANGAN,
            self::STATUS_EXECUTED_AT_KEUANGAN,
            self::STATUS_PROCESSING,
            self::STATUS_READY_PICKUP,
            self::STATUS_COMPLETED,
        ]);
    }

    public function scopeRejected($query)
    {
        return $query->whereIn('status', [
            self::STATUS_SUPERADMIN_REJECTED,
            self::STATUS_REJECT_DIREKSI,
            self::STATUS_CANCELLED,
        ]);
    }

    public function canUpdateStatus(): bool
    {
        return !in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ]);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENGAJUAN_TERKIRIM,
            self::STATUS_PENDING_ADMIN_REVIEW,
            self::STATUS_DIAJUKAN_KE_SUPERADMIN,
            self::STATUS_PENGAJUAN_DIKIRIM_KE_DIREKSI,
        ]);
    }

    public function getNextPossibleStatuses(): array
    {
        return match ($this->status) {
            self::STATUS_PENGAJUAN_TERKIRIM => [
                self::STATUS_PENDING_ADMIN_REVIEW => 'Kirim ke Admin untuk Review',
                self::STATUS_CANCELLED => 'Batalkan',
            ],
            self::STATUS_PENDING_ADMIN_REVIEW => [
                self::STATUS_DIAJUKAN_KE_SUPERADMIN => 'Kirim ke Pengadaan',
                self::STATUS_CANCELLED => 'Tolak',
            ],
            self::STATUS_DIAJUKAN_KE_SUPERADMIN => [
                self::STATUS_SUPERADMIN_APPROVED => 'Setujui Pengadaan',
                self::STATUS_PENGAJUAN_DIKIRIM_KE_DIREKSI => 'Kirim ke Direksi',
                self::STATUS_SUPERADMIN_REJECTED => 'Tolak Pengadaan',
            ],
            self::STATUS_SUPERADMIN_APPROVED => [
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
                self::STATUS_EXECUTED_BY_KEUANGAN => 'Dieksekusi Keuangan',
            ],
            self::STATUS_EXECUTED_BY_KEUANGAN => [
                self::STATUS_PENGAJUAN_DIKIRIM_KE_PENGADAAN => 'Kirim ke Pengadaan',
            ],
            self::STATUS_PENGAJUAN_DIKIRIM_KE_PENGADAAN => [
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
            self::STATUS_PENDING_ADMIN_REVIEW => 10,
            self::STATUS_DIAJUKAN_KE_SUPERADMIN => 15,
            self::STATUS_SUPERADMIN_APPROVED => 20,
            self::STATUS_PENGAJUAN_DIKIRIM_KE_DIREKSI => 25,
            self::STATUS_APPROVED_BY_DIREKSI => 35,
            self::STATUS_PENGAJUAN_DIKIRIM_KE_KEUANGAN => 40,
            self::STATUS_PENDING_KEUANGAN => 45,
            self::STATUS_PROCESS_KEUANGAN => 50,
            self::STATUS_EXECUTE_KEUANGAN => 55,
            self::STATUS_PENGAJUAN_DIKIRIM_KE_PENGADAAN => 65,
            self::STATUS_PENGAJUAN_DIKIRIM_KE_ADMIN => 70,
            self::STATUS_PROCESSING => 80,
            self::STATUS_READY_PICKUP => 95,
            self::STATUS_COMPLETED => 100,
            self::STATUS_SUPERADMIN_REJECTED => 15,
            self::STATUS_REJECT_DIREKSI => 25,
            self::STATUS_CANCELLED => 0,
            default => 0,
        };
    }
}
