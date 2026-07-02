<?php

namespace App\Utils\Tracker;

use Illuminate\Database\Eloquent\Model;

class RoutePath extends Model
{
    protected $table = "tracker_route_paths";

    protected $fillable = [
        'route_id', 'path', 'parameters'
    ];

    protected $casts = [
        'parameters' => 'json'
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }
}