<?php

namespace App\Console\Commands\Campaign\Prospect;

use App\Console\Commands\Campaign\Action;
use App\Models\Prospect;

/**
 * Mark the prospect as not processed
 * 
 * Data format: none
 */
class ProspectNotProcessedAction extends Action
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
            'processed_at' => null,
            'processed_by' => null,
        ]);
    }
}