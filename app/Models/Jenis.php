<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jenis extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jenis';

    protected $fillable = [
        'nama_jenis',
        'kategori_id',
        'deskripsi',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'kategori_id' => 'integer',
    ];

    // Relasi ke kategori (many-to-one)
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    // Relasi ke barang (one-to-many)
    public function barang()
    {
        return $this->hasMany(Barang::class, 'jenis_id');
    }

    // Relasi ke barang aktif (tidak terhapus)
    public function barangAktif()
    {
        return $this->hasMany(Barang::class, 'jenis_id')->whereNull('deleted_at');
    }

    // Relasi ke barang masuk
    public function barangmasuk()
    {
        return $this->hasMany(Barangmasuk::class, 'jenis_id');
    }

    // Relasi ke barang keluar  
    public function barangkeluar()
    {
        return $this->hasMany(Barangkeluar::class, 'jenis_id');
    }

    // Accessor untuk mendapatkan nama lengkap (kategori+ jenis)
    public function getNamaLengkapAttribute(): string
    {
        return $this->kategori?->nama_kategori . ' - ' . $this->nama_jenis;
    }

    // Accessor untuk total barang
    public function getTotalBarangAttribute(): int
    {
        return $this->barangAktif()->count();
    }

    // Accessor untuk total stok
    public function getTotalStokAttribute(): int
    {
        return $this->barangAktif()->sum('jumlah_barang');
    }

    // Scope untuk filter berdasarkan kategori
    public function scopeByKategori($query, $kategoriId)
    {
        return $query->where('kategori_id', $kategoriId);
    }

    // Scope untuk jenis yang memiliki barang
    public function scopeWithBarang($query)
    {
        return $query->whereHas('barangAktif');
    }

    // Method untuk cek apakah jenis ini bisa dihapus
    public function canBeDeleted(): bool
    {
        return $this->barangAktif()->count() === 0;
    }
}
