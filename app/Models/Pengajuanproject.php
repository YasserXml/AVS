<?php

namespace App\Models;

use Carbon\Carbon;
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
        'rejected_by_role',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi dengan model Nameproject
     */
    public function nameproject()
    {
        return $this->belongsTo(Nameproject::class, 'project_id');
    }

    /**
     * Relasi dengan model User (user yang approve)
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relasi dengan model User (user yang reject)
     */
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Relasi dengan model User (user yang menerima)
     */
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Accessor untuk mendapatkan nama project
     */
    public function getProjectNameAttribute(): string
    {
        return $this->nameproject?->nama_project ?? 'Project tidak ditemukan';
    }

    /**
     * Accessor untuk mendapatkan nama PM
     */
    public function getProjectManagerAttribute(): string
    {
        return $this->nameproject?->user?->name ?? 'PM tidak ditemukan';
    }

    /**
     * Scope untuk filter berdasarkan project
     */
    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
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

    private function generateFallbackNomorPengajuan(): string
    {
        $prefix = 'PO-';
        $date = Carbon::now()->format('Ymd');
        $uuid = strtoupper(substr(str_replace('-', '', Str::uuid()), 0, 8));

        return $prefix . $date . '-' . $uuid;
    }

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
}
