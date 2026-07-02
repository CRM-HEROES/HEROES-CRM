<?php

namespace App\Console\Commands\Campaign\Order;

use App\Console\Commands\Campaign\Prospect\ProspectAttachUserAction as ProspectProspectAttachUserAction;

/**
 * Attach user
 * to the prospect associated to the order
 * 
 * Data format:
 * {
 *    user: {user ID},
 * }
 */
class ProspectAttachUserAction extends ProspectProspectAttachUserAction
{
    public function setModel($model)
    {
        $this->model = $model->prospect;
    }
}