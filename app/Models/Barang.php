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

    public function getSpesifikasiAttribute($value)
    {
        Log::info('Accessor spesifikasi dipanggil:', ['raw_value' => $value, 'decoded' => json_decode($value, true)]);
        return json_decode($value, true);
    }
}
