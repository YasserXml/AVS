<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kategori extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kategoris';

    protected $fillable = [
        'nama_kategori',
    ];

    // Tambahkan appends untuk menghitung otomatis
    protected $appends = [
        'total_barang',
        'total_stok'
    ];

    public function barang()
    {
        return $this->hasMany(Barang::class, 'kategori_id');
    }

    public function barangAktif()
    {
        return $this->hasMany(Barang::class, 'kategori_id')->whereNull('deleted_at');
    }

    public function barangmasuk()
    {
        return $this->hasMany(Barangmasuk::class);
    }

    // Accessor untuk menghitung total jenis barang
    public function getTotalBarangAttribute()
    {
        return $this->barangAktif()->count();
    }

    // Accessor untuk menghitung total stok semua barang
    public function getTotalStokAttribute()
    {
        return $this->barangAktif()->sum('jumlah_barang');
    }

    // Method untuk mendapatkan status stok keseluruhan kategori
    public function getStatusStokAttribute()
    {
        $totalStok = $this->total_stok;

        if ($totalStok == 0) {
            return 'kosong';
        } elseif ($totalStok < 50) { // Anda bisa sesuaikan threshold ini
            return 'menipis';
        } else {
            return 'tersedia';
        }
    }

    // Method untuk mendapatkan warna badge berdasarkan stok
    public function getWarnaStokAttribute()
    {
        return match ($this->status_stok) {
            'kosong' => 'danger',
            'menipis' => 'warning',
            'tersedia' => 'success',
            default => 'gray'
        };
    }
}
