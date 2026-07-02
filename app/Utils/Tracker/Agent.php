<?php

namespace App\Utils\Tracker;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $table = "tracker_agents";

    protected $fillable = ['browser', 'browser_version'];
}