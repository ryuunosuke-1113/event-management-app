<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceSetting extends Model
{
    protected $fillable = [
        'is_active',
        'message',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];
}