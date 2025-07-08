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
        'rejected_by_role',
        'received_by',
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
    public const STATUS_PENGAJUAN_DIKIRIM_KE_ADMIN = 'pengajuan_dikirim_ke_admin';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY_PICKUP = 'ready_pickup';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const REJECTED_BY_ADMIN = 'admin';
    public const REJECTED_BY_SUPERADMIN = 'super_admin';

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

    private function generateNomorPengajuan(): string
    {
        $maxAttempts = 10;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            try {
                return DB::transaction(function () {
                    $prefix = 'PO-';
                    $date = Carbon::now()->format('Ymd');

                    // Buat lock key berdasarkan tanggal untuk mencegah race condition
                    $lockKey = "pengajuan_nomor_lock_{$date}";

                    // Gunakan advisory lock PostgreSQL untuk mencegah race condition
                    DB::selectOne("SELECT pg_advisory_xact_lock(hashtext(?))", [$lockKey]);

                    // Cari nomor terakhir dengan pattern yang lebih spesifik
                    $lastRecord = self::where('nomor_pengajuan', 'like', $prefix . $date . '-%')
                        ->orderByRaw("CAST(SUBSTRING(nomor_pengajuan FROM '-(\\d+)$') AS INTEGER) DESC")
                        ->first();

                    if ($lastRecord) {
                        // Extract sequence number dari nomor terakhir
                        preg_match('/(\d+)$/', $lastRecord->nomor_pengajuan, $matches);
                        $lastSequence = isset($matches[1]) ? (int) $matches[1] : 0;
                        $sequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
                    } else {
                        $sequence = '0001';
                    }

                    $nomorPengajuan = $prefix . $date . '-' . $sequence;

                    // Double check apakah nomor sudah ada (safety check)
                    if (self::where('nomor_pengajuan', $nomorPengajuan)->exists()) {
                        throw new \Exception("Nomor pengajuan {$nomorPengajuan} sudah ada");
                    }

                    return $nomorPengajuan;
                });
            } catch (\Exception $e) {
                $attempt++;

                // Jika masih ada kesempatan, tunggu sebentar dan coba lagi
                if ($attempt < $maxAttempts) {
                    // Random delay untuk menghindari thundering herd
                    usleep(mt_rand(10000, 100000)); // 10-100ms
                    continue;
                }

                // Jika sudah maksimal attempt, gunakan fallback dengan UUID
                Log::warning("Gagal generate nomor pengajuan setelah {$maxAttempts} percobaan: " . $e->getMessage());
                return $this->generateFallbackNomorPengajuan();
            }
        }

        // Fallback jika semua percobaan gagal
        return $this->generateFallbackNomorPengajuan();
    }

    /**
     * Generate nomor pengajuan fallback menggunakan UUID
     */
    private function generateFallbackNomorPengajuan(): string
    {
        $prefix = 'PO-';
        $date = Carbon::now()->format('Ymd');
        $uuid = strtoupper(substr(str_replace('-', '', Str::uuid()), 0, 8));

        return $prefix . $date . '-' . $uuid;
    }

    /**
     * Alternative method: Generate nomor pengajuan dengan database sequence
     * Lebih robust tapi butuh perubahan database
     */
    public static function generateNomorPengajuanWithSequence(): string
    {
        $prefix = 'PO-';
        $date = Carbon::now()->format('Ymd');

        // Buat sequence name berdasarkan tanggal
        $sequenceName = "pengajuan_seq_{$date}";

        try {
            // Coba buat sequence jika belum ada
            DB::statement("CREATE SEQUENCE IF NOT EXISTS {$sequenceName} START 1");

            // Ambil next value dari sequence
            $nextVal = DB::selectOne("SELECT nextval('{$sequenceName}') as next_val")->next_val;
            $sequence = str_pad($nextVal, 4, '0', STR_PAD_LEFT);

            return $prefix . $date . '-' . $sequence;
        } catch (\Exception $e) {
            Log::error("Error generating nomor pengajuan dengan sequence: " . $e->getMessage());

            // Fallback ke method lama
            return (new self)->generateNomorPengajuan();
        }
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENGAJUAN_TERKIRIM => 'Pengajuan Terkirim',
            self::STATUS_PENDING_ADMIN_REVIEW => 'Menunggu Review Admin',
            self::STATUS_DIAJUKAN_KE_SUPERADMIN => 'Diajukan ke Pengadaan',
            self::STATUS_SUPERADMIN_APPROVED => 'Disetujui Pengadaan',
            self::STATUS_SUPERADMIN_REJECTED => 'Ditolak Pengadaan',
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
            self::STATUS_PENGAJUAN_DIKIRIM_KE_ADMIN,
        ]);
    }

    public function scopeApproved($query)
    {
        return $query->whereIn('status', [
            self::STATUS_SUPERADMIN_APPROVED,
            self::STATUS_PROCESSING,
            self::STATUS_READY_PICKUP,
            self::STATUS_COMPLETED,
        ]);
    }

    public function scopeRejected($query)
    {
        return $query->whereIn('status', [
            self::STATUS_SUPERADMIN_REJECTED,
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
                self::STATUS_SUPERADMIN_APPROVED => 'Setujui (Pengadaan)',
                self::STATUS_CANCELLED => 'Tolak (Pengadaan)',
            ],
            self::STATUS_SUPERADMIN_APPROVED => [
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
            self::STATUS_PENGAJUAN_TERKIRIM => 10,
            self::STATUS_PENDING_ADMIN_REVIEW => 15,
            self::STATUS_DIAJUKAN_KE_SUPERADMIN => 30,
            self::STATUS_SUPERADMIN_APPROVED => 50,
            self::STATUS_PROCESSING => 75,
            self::STATUS_READY_PICKUP => 95,
            self::STATUS_COMPLETED => 100,
            self::STATUS_SUPERADMIN_REJECTED, self::STATUS_CANCELLED => 0,
            default => 0,
        };
    }
}
