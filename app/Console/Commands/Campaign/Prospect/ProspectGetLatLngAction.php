<?php

namespace App\Console\Commands\Campaign\Prospect;

use App\Console\Commands\Campaign\Action;
use App\Jobs\ProspectGetLatLng;
use App\Models\Prospect;

/**
 * Retrieve prospect lat lng
 * 
 * Data format: none
 */
class ProspectGetLatLngAction extends Action
{
    /**
     * 
     */
    public function handle()
    {
        if (!$this->model instanceof Prospect) {
            return;
        }
        
        ProspectGetLatLng::dispatch($this->model);
    }
}