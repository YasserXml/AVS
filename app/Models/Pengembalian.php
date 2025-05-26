<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengembalian extends Model
{
    protected $table ='penegembalians';

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
