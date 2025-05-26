<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barangkeluar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'barangkeluars';

    protected $fillable = [
       'jumlah_barang_keluar',
       'tanggal_keluar_barang',
       'keterangan',
       'status',
       'barang_id',
       'pengajuan_id',
       'user_id',
    ];

    protected $casts = [
        'tanggal_barang_keluar' => 'date',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
}
