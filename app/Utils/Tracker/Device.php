<?php

namespace App\Utils\Tracker;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $table = "tracker_devices";

    protected $fillable = [
        'kind', 'model', 'platform', 'platform_version', 'is_mobile'
    ];
}