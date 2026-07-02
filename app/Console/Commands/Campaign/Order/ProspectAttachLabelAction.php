<?php

namespace App\Console\Commands\Campaign\Order;

use App\Console\Commands\Campaign\Prospect\ProspectAttachLabelAction as ProspectProspectAttachLabelAction;

/**
 * Attach label
 * to the prospect associated to the order
 * 
 * Data format:
 * {
 *    label: {label ID},
 * }
 */
class ProspectAttachLabelAction extends ProspectProspectAttachLabelAction
{
    public function setModel($model)
    {
        $this->model = $model->prospect;
    }
}