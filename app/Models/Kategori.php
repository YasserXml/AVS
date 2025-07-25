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

    public function barang()
    {
       return $this->hasMany(Barang::class, 'kategori_id');
    }

    public function barangmasuk()
    {
        return $this->hasMany(Barangmasuk::class);
    }

    public function pengajuan()
    {
        return $this->hasMany(Pengajuan::class);
    }
}
