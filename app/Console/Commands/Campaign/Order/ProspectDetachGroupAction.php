<?php

namespace App\Console\Commands\Campaign\Order;

use App\Console\Commands\Campaign\Prospect\ProspectDetachGroupAction as ProspectProspectDetachGroupAction;

/**
 * Detach group
 * from the prospect associated to the order
 * 
 * Data format:
 * {
 *    group: {group ID},
 * }
 */
class ProspectDetachGroupAction extends ProspectProspectDetachGroupAction
{
    public function setModel($model)
    {
        $this->model = $model->prospect;
    }
}