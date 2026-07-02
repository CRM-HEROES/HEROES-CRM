<?php

namespace App\Utils\Tracker;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $table = "tracker_routes";

    protected $fillable = [
        'name', 'action'
    ];
}