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
        'kategori_id',
        'subkategori_id',
    ];

    protected $casts = [
        'tanggal_barang_masuk' => 'date', // Diperbaiki    tanggal_masuk_barang
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

    public function subkategori()
    {
        return $this->belongsTo(Subkategori::class);
    }

    public function getTotalStockSamaAttribute()
    {
        return Barang::where('nama_barang', $this->barang->nama_barang)
            ->sum('jumlah_barang');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByKategori($query, $kategoriId)
    {
        return $query->where('kategori_id', $kategoriId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_barang_masuk', [$startDate, $endDate]);
    }
}
