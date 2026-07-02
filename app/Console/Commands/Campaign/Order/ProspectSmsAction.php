<?php

namespace App\Console\Commands\Campaign\Order;

use App\Console\Commands\Campaign\Prospect\ProspectSmsAction as ProspectProspectSmsAction;

/**
 * Send sms to the prospect
 * 
 * Data format:
 * {
 *    message: {body of the sms},
 *    source: {source, may be 'smsbox' or 'ultramsg' ...},
 * }
 */
class ProspectSmsAction extends ProspectProspectSmsAction
{
    public function setModel($model)
    {
        $this->model = $model->prospect;
    }
}