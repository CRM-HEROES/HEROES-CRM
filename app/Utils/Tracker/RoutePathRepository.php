<?php

namespace App\Utils\Tracker;

use Illuminate\Http\Request;

class RoutePathRepository
{
    protected $model;
    protected $routeRepository;

    public function __construct(RoutePath $model, RouteRepository $routeRepository)
    {
        $this->model = $model;
        $this->routeRepository = $routeRepository;
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

    public function getId($route, $request)
    {
        $route_id = $this->getRouteId(
            $this->getRouteName($route),
            $this->getRouteAction($route) ?: 'closure'
        );

        $route_path = $this->getRoutePath($route_id, $this->getRequestPath($request), $route);

        return $route_path->id;
    }

    protected function getRoutePath($route_id, $path, $route)
    {
        return $this->firstOrCreate(
            ['route_id' => $route_id, 'path' => $path, 'parameters' => $this->getRouteParameters($route)],
            ['route_id', 'path']
        );
    }

    protected function getRouteId($name, $action)
    {
        return $this->routeRepository->firstOrCreate(
            ['name' => $name, 'action' => $action],
            ['name', 'action']
        )->id;
    }

    protected function getRouteParameters($route)
    {
        $parameters = [];

        foreach ($route->current()->parameters() as $parameter => $value) {
            if ($value instanceof \Illuminate\Database\Eloquent\Model) {
                $parameters[$parameter] = $value->getRouteKey();
            } else {
                $parameters[$parameter] = $value;
            }
        }

        return $parameters;
    }

    private function getRouteAction($route)
    {
        if (is_string($route)) {
            return '';
        }

        if (is_array($route)) {
            return $route['action'];
        }

        return $route->currentRouteAction();
    }

    protected function getRouteName($route)
    {
        if (is_string($route)) {
            return $route;
        }

        if (is_array($route)) {
            return $route['name'];
        }

        if ($name = $route->current()->getName()) {
            return $name;
        }

        $action = $route->current()->getAction();

        if ($name = isset($action['as']) ? $action['as'] : null) {
            return $name;
        }

        return '/' . $route->current()->uri();
    }

    protected function getRequestPath($request)
    {
        if (is_string($request)) {
            return $request;
        }

        if (is_array($request)) {
            return $request['path'];
        }

        return $request->path();
    }
}