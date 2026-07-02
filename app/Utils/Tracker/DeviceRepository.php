<?php

namespace App\Utils\Tracker;

use App\Events\UserDeviceCreated;
use App\Utils\Tracker\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use Jenssegers\Agent\Agent;

class DeviceRepository
{
    protected $model;
    protected $request;
    protected $agent;

    public function __construct(Device $model, Request $request, Agent $agent)
    {
        $this->model = $model;
        $this->request = $request;
        $this->agent = $agent;
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
            
            App::terminating(function() use($model) {
                UserDeviceCreated::dispatch($model);
            });
        }

        return $model;
    }

    public function getId()
    {
        return $this->firstOrCreate($this->getCurrentDevice(), ['kind', 'model', 'platform', 'platform_version'])->id;
    }

    protected function getCurrentDevice()
    {
        return [
            'kind' => $this->getAgentKind(),
            'model' => $this->getAgentModel(),
            'platform' => $this->getAgentPlatform(),
            'platform_version' => $this->getAgentPlatformVersion(),
            'is_mobile' => $this->isMobile(),
        ];
    }

    protected function getAgentKind()
    {
        if ($this->isTablet()) {
            return 'Tablet';
        }
        if ($this->isPhone()) {
            return 'Phone';
        }
        if ($this->isComputer()) {
            return 'Computer';
        }

        return 'Unavailable';
    }

    protected function getAgentModel()
    {
        return $this->agent->device();
    }

    protected function getAgentPlatform()
    {
        return $this->agent->platform();
    }

    protected function getAgentPlatformVersion()
    {
        return $this->agent->version($this->getAgentPlatform());
    }

    protected function isMobile()
    {
        return $this->agent->isMobile();
    }

    protected function isComputer()
    {
        return ! $this->agent->isMobile();
    }

    protected function isTablet()
    {
        return $this->agent->isTablet();
    }

    protected function isPhone()
    {
        return ! $this->isTablet() && ! $this->isComputer();
    }
}