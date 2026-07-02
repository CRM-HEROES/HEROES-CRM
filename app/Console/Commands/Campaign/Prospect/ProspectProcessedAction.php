<?php

namespace App\Console\Commands\Campaign\Prospect;

use App\Console\Commands\Campaign\Action;
use App\Models\Prospect;

/**
 * Mark the prospect as processed
 * 
 * Data format: none
 */
class ProspectProcessedAction extends Action
{
    /**
     * 
     */
    public function handle()
    {
        if (!$this->model instanceof Prospect) {
            return;
        }
        
        $this->model->update([
            'processed_at' => \Carbon\Carbon::now()
        ]);
    }
}