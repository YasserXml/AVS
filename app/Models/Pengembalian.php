<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pengembalian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table ='pengembalians';

    protected $fillable = [
        'pengajuan_id',
        'barang_id',
        'user_id',
        'jumlah_dikembalikan',
        'tanggal_pengembalian',
        'kondisi',
        'keterangan',
        'approve_by',
        'approved_at',
    ];

    public function barang()
    {
       return  $this->belongsTo(Barang::class, 'barang_id');
    }

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }

    public function user()
    {
        return $this->belongsto(User::class, 'user_id');
    }
}
