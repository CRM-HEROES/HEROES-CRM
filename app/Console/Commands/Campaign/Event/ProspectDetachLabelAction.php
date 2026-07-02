<?php

namespace App\Console\Commands\Campaign\Event;

use App\Console\Commands\Campaign\Prospect\ProspectDetachLabelAction as ProspectProspectDetachLabelAction;

/**
 * Detach label
 * from the prospect associated to the order
 * 
 * Data format:
 * {
 *    label: {label ID},
 * }
 */
class ProspectDetachLabelAction extends ProspectProspectDetachLabelAction
{
    public function setModel($model)
    {
        $this->model = $model->prospect;
    }
}