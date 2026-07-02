<?php

namespace App\Utils\Tracker;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = "tracker_logs";

    protected $fillable = [
        'user_id', 'session_id', 'method', 'route_path_id', 'is_ajax', 'is_secure', 'is_json', 'wants_json'
    ];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function routePath()
    {
        return $this->belongsTo(RoutePath::class);
    }
}