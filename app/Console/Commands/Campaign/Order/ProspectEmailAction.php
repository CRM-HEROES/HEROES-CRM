<?php

namespace App\Console\Commands\Campaign\Order;

use App\Console\Commands\Campaign\Prospect\ProspectEmailAction as ProspectProspectEmailAction;

/**
 * Send an email to the prospect associated to the order
 *
 * Data format:
 * {
 *    subject: {subject of the email},
 *    body: {HTML body of the email},
 * }
 */
class ProspectEmailAction extends ProspectProspectEmailAction
{
    public function setModel($model)
    {
        $this->model = $model->prospect;
    }
}
