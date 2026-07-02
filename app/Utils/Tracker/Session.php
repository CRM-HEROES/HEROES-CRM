<?php

namespace App\Utils\Tracker;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $table = "tracker_sessions";

    protected $fillable = ['uuid', 'user_id', 'device_id', 'agent_id', 'client_ip', 'cookie_id', 'geoip_id', 'is_robot', 'language_id'];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function cookie()
    {
        return $this->belongsTo(Cookie::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function geoIp()
    {
        return $this->belongsTo(GeoIp::class, 'geoip_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function log()
    {
        return $this->belongsTo(Log::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}