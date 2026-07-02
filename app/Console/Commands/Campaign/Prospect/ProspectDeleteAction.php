<?php

namespace App\Console\Commands\Campaign\Prospect;

use App\Console\Commands\Campaign\Action;
use App\Models\Prospect;

/**
 * Delete the prospect
 * 
 * Data format: none
 */
class ProspectDeleteAction extends Action
{
    /**
     * 
     */
    public function handle()
    {
        if (!$this->model instanceof Prospect) {
            return;
        }
        
        $this->model->delete();
    }
}