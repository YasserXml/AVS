<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama_project',
        'user_id',
        'barang_id',
        'kategori_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'client_name',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    public function barangs()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kategoris()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }
}
