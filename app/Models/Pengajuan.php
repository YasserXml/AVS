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
    ];

    protected $casts =[
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

    public function detailpengajuan()
    {
        return $this->hasMany(Detailpengajuan::class);
    }
}
