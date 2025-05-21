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
        'tanggal_barang_masuk',
        'jumlah_barang_masuk',
        'status',
        'dibeli',
        'project_name',
    ];

    protected $casts = [
        'tanggal_masuk_barang' => 'datetime',
        
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
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    
}
