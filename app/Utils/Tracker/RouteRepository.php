<?php

namespace App\Utils\Tracker;

use Illuminate\Http\Request;

class RouteRepository
{
    protected $model;

    public function __construct(Route $model)
    {
        $this->model = $model;
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
}