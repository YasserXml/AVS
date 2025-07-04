<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class Pengajuanoprasional extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nomor_pengajuan',
        'batch_id',
        'user_id',
        'user_id',
        'tanggal_pengajuan',
        'tanggal_dibutuhkan',
        'detail_barang',
        'uploaded_files',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'reject_reason',
        'rejected_by_role',
        'received_by',
        'status_history',
    ];
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->user_id = Auth::id();
            $model->nomor_pengajuan = 'PO-' . Str::upper(Str::random(8));
        });

        static::addGlobalScope('user', function (Builder $builder) {
            $builder->where('user_id', Auth::id());
        });
    }
    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'tanggal_dibutuhkan' => 'date',
        'detail_barang' => 'array',
        'uploaded_files' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'status_history' => 'array',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
