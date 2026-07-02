<?php

namespace App\Utils\Tracker;

class LogRepository
{
    protected $model;
    protected $route_path_id;
    protected $log;

    public function __construct(Log $model)
    {
        $this->model = $model;
    }

    public function create($data)
    {
        $this->log = $this->model->create($data);

        $this->updateRoute();

        return $this->log;
    }

    protected function updateRoute($route_path_id = null)
    {
        if ($route_path_id) {
            $this->route_path_id = $route_path_id;
        }

        if ($this->log->id && $this->route_path_id && ! $this->log->route_path_id) {
            $this->log->route_path_id = $this->route_path_id;

            $this->log->save();
        }

        return $this->log;
    }
}