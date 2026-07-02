<?php

namespace App\Utils\Tracker;

use App\Utils\Tracker\Agent as AgentModel;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class AgentRepository
{
    protected $model;
    protected $request;
    protected $agent;

    public function __construct(AgentModel $model, Request $request, Agent $agent)
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
        }

        return $model;
    }

    public function getId()
    {
        return $this->firstOrCreate($this->getCurrentAgent())->id;
    }

    protected function getCurrentAgent()
    {
        return [
            'browser' => $this->getAgentBrowser(),
            'browser_version' => $this->getAgentBrowserVersion()
        ];
    }

    protected function getAgentBrowser()
    {
        return $this->agent->browser();
    }

    protected function getAgentBrowserVersion()
    {
        return  $this->agent->version($this->getAgentBrowser());
    }
}