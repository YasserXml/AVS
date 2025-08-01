<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Barang extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'barangs';

    protected $fillable = [
        'serial_number',
        'kode_barang',
        'nama_barang',
        'jumlah_barang',
        'harga_barang',
        'subkategori_id',
        'kategori_id',
        'spesifikasi',
    ];

    protected $casts = [
        'spesifikasi' => 'array',
        'deleted_at' => 'datetime',
    ];

    public function barangmasuk()
    {
        return $this->hasMany(Barangmasuk::class);
    }

    public function barangkeluar()
    {
        return $this->hasMany(Barangkeluar::class);
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function asetpt()
    {
        return $this->hasMany(Asetpt::class);
    }

    public function subkategori()
    {
        return $this->belongsTo(Subkategori::class, 'subkategori_id');
    }

    public function getSpesifikasiAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getKategoriLengkapAttribute()
    {
        $kategori = $this->kategori->nama_kategori ?? '';
        $subkategori = $this->subkategori->nama_subkategori ?? '';

        return $subkategori ? "{$kategori} - {$subkategori}" : $kategori;
    }

    // Scope untuk filter berdasarkan kategori
    public function scopeByKategori($query, $kategoriId)
    {
        return $query->where('kategori_id', $kategoriId);
    }

    // Scope untuk filter berdasarkan subkategori
    public function scopeBySubkategori($query, $subkategoriId)
    {
        return $query->where('subkategori_id', $subkategoriId);
    }
}
