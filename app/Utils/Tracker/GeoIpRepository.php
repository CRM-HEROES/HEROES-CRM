<?php

namespace App\Utils\Tracker;

use App\Utils\Tracker\GeoIp as GeoIpModel;
use Illuminate\Http\Request;

class GeoIpRepository
{
    protected $model;
    protected $request;

    public function __construct(GeoIpModel $model, Request $request)
    {
        $this->model = $model;
        $this->request = $request;
    }

    protected function newQuery()
    {
        return $this->model->newQuery();
    }

    public function firstOrCreate($attributes, $keys = null)
    {
        $keys = $keys ?: array_keys($attributes);
        $model = $this->newQuery();

        foreach ($keys as $key) {
            $model->where($key, $attributes[$key]);
        }

        if (! $model = $model->first()) {
            $model = $this->newQuery()->create($attributes);
        }

        return $model;
    }

    public function getId($clientIp)
    {
	return null;
        if ($geoIpData = $this->getGeoIpData($clientIp)) {
            $geoIp = $this->firstOrCreate($geoIpData, ['latitude', 'longitude']);

            return $geoIp->id;
        }

        return null;
    }

    protected function getGeoIpData($clientIp)
    {
        $data = geoip($clientIp);

        return [
            'latitude' => $data->lat,
            'longitude' => $data->lon,
            'country_code' => $data->iso_code,
            'country_name' => $data->country,
            'region' => $data->continent,
            'city' => $data->city,
            'postal_code' => $data->postal_code,
        ];
    }
}
