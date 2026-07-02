<?php

namespace App\Console\Commands\Campaign\Event;

use App\Console\Commands\Campaign\Prospect\ProspectNotProcessedAction as ProspectProspectNotProcessedAction;

/**
 * Mark the prospect as not processed
 * 
 * Data format: none
 */
class ProspectNotProcessedAction extends ProspectProspectNotProcessedAction
{
    public function setModel($model)
    {
        $this->model = $model->prospect;
    }
}