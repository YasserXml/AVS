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
  public function pengajuanproject()
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
}
 