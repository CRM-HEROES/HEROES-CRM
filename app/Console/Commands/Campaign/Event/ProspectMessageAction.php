<?php

namespace App\Console\Commands\Campaign\Event;

use App\Console\Commands\Campaign\Prospect\ProspectMessageAction as ProspectProspectMessageAction;

/**
 * Send message to a thread associated to
 * the prospect associated to the order
 * 
 * Data format:
 * {
 *    thread: {thread ID, thread in which we send the message},
 *    body: {body, body of the message},
 * }
 */
class ProspectMessageAction extends ProspectProspectMessageAction
{
    public function setModel($model)
    {
        $this->model = $model->prospect;
    }
}