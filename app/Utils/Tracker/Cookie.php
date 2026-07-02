<?php

namespace App\Utils\Tracker;

use Illuminate\Database\Eloquent\Model;

class Cookie extends Model
{
    protected $table = "tracker_cookies";

    protected $fillable = ['uuid'];
}