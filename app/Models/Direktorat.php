<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Direktorat extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $table = 'direktorats';

    protected $fillable = [
        'nama',
        'date_modified',
        'type',
        'size',
    ];
    
    protected $casts = [
        'date_modified' => 'datetime',
    ];
}
