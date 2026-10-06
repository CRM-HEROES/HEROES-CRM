<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServerMetric extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'recorded_at' => 'datetime',
        'details' => 'array',
    ];
}
