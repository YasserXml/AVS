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

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function barang()
    {
        return $this->hasMany(Barang::class);
    }
}
