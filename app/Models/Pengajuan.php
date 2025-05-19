<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pengajuan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pengajuans';

    protected $fillable = [
        'jumlah_barang_diajukan',
        'status',
        'tanggal_pengajuan',
        'keterangan',
        'approved_at',
        'reject_reason',
        'barang_id',
        'user_id',
        'kategoris_id',
        'approved_by',
        'reject_by',
    ];

    protected $casts =[
        'jumlah_barang_diajukan' => 'integer',
        'tanggal_pengajuan' => 'datetime',
        'approved_at' => 'datetime',
        'reject_reason' => 'string',
    ];
}
