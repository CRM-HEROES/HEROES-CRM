<?php

namespace App\Console\Commands\Campaign\Order;

use App\Console\Commands\Campaign\Prospect\ProspectDeleteAction as ProspectProspectDeleteAction;

/**
 * Delete the prospect associated prospect
 * 
 * Data format: none
 */
class ProspectDeleteAction extends ProspectProspectDeleteAction
{
    public function setModel($model)
    {
        $this->model = $model->prospect;
    }
}