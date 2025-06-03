<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DivisiRnd extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'divisirnds';

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
