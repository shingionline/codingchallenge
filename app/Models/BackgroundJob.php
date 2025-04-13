<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackgroundJob extends Model
{
    protected $fillable = [
        'class',
        'method',
        'params',
        'status',
        'started_at',
        'completed_at',
        'error'
    ];

    protected $casts = [
        'params' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];
} 