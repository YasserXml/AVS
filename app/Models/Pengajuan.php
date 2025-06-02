<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pengajuan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pengajuans';

    protected $fillable = [
        'nama_barang', 
        'detail_barang',
        'Jumlah_barang_diajukan',
        'status',
        'tanggal_pengajuan',
        'keterangan',
        'approved_at',
        'reject_reason',
        'barang_id',
        'user_id',
        'kategoris_id',
        'approved_by',
        'reject_by',
        'barang_keluar_id',
        'status_barang',
        'nama_project',
        'batch_id',
    ];

    protected $casts = [
        'jumlah_barang_diajukan' => 'integer',
        'tanggal_pengajuan' => 'datetime',
        'approved_at' => 'datetime',
        'reject_reason' => 'string',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategoris_id');
    }

    public function barangKeluar()
    {
        return $this->belongsTo(Barangkeluar::class, 'barang_keluar_id');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'reject_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function batchSiblings()
    {
        return $this->hasMany(Pengajuan::class, 'batch_id', 'batch_id')
            ->where('id', '!=', $this->id);
    }

    public function groupMembers()
    {
        return $this->hasMany(Pengajuan::class, 'batch_id', 'batch_id');
    }

    // Scopes
    public function scopeInSameGroup($query, $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Helper methods
    public function isPartOfGroup(): bool
    {
        if (empty($this->batch_id)) {
            return false;
        }

        return static::where('batch_id', $this->batch_id)->count() > 1;
    }

    public function getGroupSize(): int
    {
        if (empty($this->batch_id)) {
            return 1;
        }

        return static::where('batch_id', $this->batch_id)->count();
    }

    public function getGroupName(): string
    {
        if (!$this->isPartOfGroup()) {
            return 'Pengajuan Tunggal';
        }

        $tanggal = $this->tanggal_pengajuan->format('d/m');
        $pemohon = $this->user->name ?? 'Unknown';
        
        return "{$pemohon} - {$tanggal}";
    }

    // Accessor untuk status yang lebih readable
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusBarangLabelAttribute(): string
    {
        $labels = [
            'oprasional_kantor' => 'Operasional Kantor',
            'project' => 'Project',
        ];

        $statusText = $labels[$this->status_barang] ?? $this->status_barang;

        if ($this->status_barang === 'project' && !empty($this->nama_project)) {
            $statusText .= ' - ' . $this->nama_project;
        }

        return $statusText;
    }
}
