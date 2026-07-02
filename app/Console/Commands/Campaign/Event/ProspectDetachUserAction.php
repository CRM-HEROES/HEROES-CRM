<?php

namespace App\Console\Commands\Campaign\Event;

use App\Console\Commands\Campaign\Prospect\ProspectDetachUserAction as ProspectProspectDetachUserAction;

/**
 * Detach user
 * from the prospect associated to the order
 * 
 * Data format:
 * {
 *    user: {user ID},
 * }
 */
class ProspectDetachUserAction extends ProspectProspectDetachUserAction
{
    public function setModel($model)
    {
        $this->model = $model->prospect;
    }
}