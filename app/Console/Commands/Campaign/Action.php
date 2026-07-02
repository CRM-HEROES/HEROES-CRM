<?php

namespace App\Console\Commands\Campaign;

use App\Models\CampaignAction;

abstract class Action
{
    protected CampaignAction $action;
    protected $model;

    /**
     * 
     */
    public function setAction(CampaignAction $action)
    {
        $this->action = $action;
    }
    
    /**
     * 
     */
    public function setModel($model)
    {
        $this->model = $model;
    }
    
    /**
     * 
     */
    public function getAction()
    {
        return $this->action;
    }
    
    /**
     * 
     */
    public function getModel()
    {
        return $this->model;
    }
    
    /**
     * 
     */
    abstract public function handle();
}