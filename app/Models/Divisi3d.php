<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Divisi3d extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'divisi3ds';

    protected $fillable =[
        'nama',
        'date_modified',
        'type',
        'size',
    ];
}
