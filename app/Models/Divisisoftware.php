<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Divisisoftware extends Model
{
    protected $table = 'divisisoftware';

    protected $fillable = [
        'nama',
        'keterangan',
        'created_at',
        'updated_at'
    ];
}
