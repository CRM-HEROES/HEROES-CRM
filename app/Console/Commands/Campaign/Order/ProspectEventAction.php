<?php

namespace App\Console\Commands\Campaign\Order;

use App\Console\Commands\Campaign\Prospect\ProspectEventAction as ProspectProcessedAction;

/**
 * Mark the prospect as processed
 * 
 * Data format: none
 */
class ProspectEventAction extends ProspectProcessedAction
{
    public function setModel($model)
    {
        $this->model = $model->prospect;
    }
}