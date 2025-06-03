<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Divisisoftware extends Model
{
    protected $table = 'divisisoftware';

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
