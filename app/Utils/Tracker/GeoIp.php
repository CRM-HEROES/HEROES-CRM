<?php

namespace App\Utils\Tracker;

use Illuminate\Database\Eloquent\Model;

class GeoIp extends Model
{
    protected $table = "tracker_geoip";

    protected $fillable = [
        'latitude', 'longitude', 'country_code', 'country_name', 'region', 'city', 'postal_code'
    ];

    protected $appends = [
        'full_address'
    ];

    public function getFullAddressAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return "{$this->city} in {$this->country_name} [{$this->region}]";
        }

        return "Local";
    }
}