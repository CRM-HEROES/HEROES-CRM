<?php

namespace App\Console\Commands\Campaign\Event;

use App\Console\Commands\Campaign\Prospect\ProspectAttachGroupAction as ProspectProspectAttachGroupAction;

/**
 * Attach group
 * to the prospect associated to the order
 * 
 * Data format:
 * {
 *    group: {group ID},
 * }
 */
class ProspectAttachGroupAction extends ProspectProspectAttachGroupAction
{
    public function setModel($model)
    {
        $this->model = $model->prospect;
    }
}