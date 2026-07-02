<?php

namespace App\Console\Commands\Campaign\Order;

use App\Console\Commands\Campaign\Prospect\ProspectProcessedAction as ProspectProspectProcessedAction;

/**
 * Mark the prospect as processed
 * 
 * Data format: none
 */
class ProspectProcessedAction extends ProspectProspectProcessedAction
{
    public function setModel($model)
    {
        $this->model = $model->prospect;
    }
}