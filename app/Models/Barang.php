<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'kategori_id',
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
}
