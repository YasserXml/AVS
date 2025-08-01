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

    public function subkategoris()
    {
        return $this->hasMany(Subkategori::class);
    }

    public function barangmasuk()
    {
        return $this->hasMany(Barangmasuk::class);
    }

    public function barangkeluar()
    {
        return $this->hasMany(Barangkeluar::class);
    }

    public function barangAktif()
    {
        return $this->barang()->whereNull('deleted_at');
    }
}
