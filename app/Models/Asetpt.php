<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asetpt extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'asetpts';

    protected $fillable = [
        'tanggal',
        'nama_barang',
        'qty',
        'brand',
        'status',
        'pic',
        'kondisi',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'qty' => 'integer',
        'status' => 'string',
        'kondisi' => 'string',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
