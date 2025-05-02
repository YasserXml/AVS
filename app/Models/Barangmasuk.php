<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barangmasuk extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'serial_number',
        'kode_barang',
        'user_id',
        'barang_id',
        'tanggal_masuk_barang',
        'jumlah_barang_masuk',
        'status',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }
}
