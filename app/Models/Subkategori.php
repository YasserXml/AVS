<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subkategori extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'subkategoris';

    protected $fillable = [
        'nama_subkategori',
        'kategori_id',
    ];

    // Tambahan appends untuk otomatis load attribute
    protected $appends = ['total_barang'];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function barang()
    {
        return $this->hasMany(Barang::class);
    }

    public function barangkeluar()
    {
        return $this->hasMany(BarangKeluar::class);
    }

    public function barangmasuk()
    {
        return $this->hasMany(BarangMasuk::class);
    }

    // Method untuk menghitung total barang di subkategori ini
    public function getTotalBarangAttribute()
    {
        return $this->barang()->sum('jumlah_barang');
    }

    // Method scope untuk eager loading dengan total
    public function scopeWithTotalBarang($query)
    {
        return $query->withSum('barang', 'jumlah_barang');
    }

    // Method untuk mendapatkan nama dengan total
    public function getNamaWithTotalAttribute()
    {
        $total = $this->total_barang;
        return "{$this->nama_subkategori} ({$total})";
    }
}
