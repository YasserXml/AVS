<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nameproject extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'nama_project',
    'user_id',
    'tanggal_mulai',
    'tanggal_selesai',
  ];

  protected $casts = [
    'tanggal_mulai' => 'datetime',
    'tanggal_selesai' => 'datetime',
  ];

  /**
   * Relasi dengan model User (Project Manager)
   */
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Relasi dengan model Pengajuanproject
   */
  public function pengajuanprojects()
  {
    return $this->hasMany(Pengajuanproject::class, 'project_id');
  }

  /**
   * Accessor untuk mendapatkan nama PM
   */
  public function getProjectManagerAttribute(): string
  {
    return $this->user?->name ?? 'Tidak ada PM';
  }

  /**
   * Accessor untuk mendapatkan status project
   */
  public function getStatusAttribute(): string
  {
    if (!$this->tanggal_mulai || !$this->tanggal_selesai) {
      return 'Belum Dijadwalkan';
    }

    $now = now();

    if ($now < $this->tanggal_mulai) {
      return 'Belum Dimulai';
    } elseif ($now >= $this->tanggal_mulai && $now <= $this->tanggal_selesai) {
      return 'Sedang Berjalan';
    } else {
      return 'Selesai';
    }
  }

  /**
   * Scope untuk project yang sedang aktif
   */
  public function scopeActive($query)
  {
    return $query->where('tanggal_mulai', '<=', now())
      ->where('tanggal_selesai', '>=', now());
  }

  /**
   * Scope untuk project yang belum dimulai
   */
  public function scopeUpcoming($query)
  {
    return $query->where('tanggal_mulai', '>', now());
  }

  /**
   * Scope untuk project yang sudah selesai
   */
  public function scopeCompleted($query)
  {
    return $query->where('tanggal_selesai', '<', now());
  }
}
