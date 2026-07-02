<?php

namespace App\Console\Commands\Campaign\Prospect;

use App\Console\Commands\Campaign\Action;
use App\Models\Prospect;

/**
 * Send sms to the prospect
 * 
 * Data format:
 * {
 *    message: {body of the sms},
 *    source: {source, may be 'smsbox' or 'ultramsg' ...},
 * }
 */
class ProspectSmsAction extends Action
{
    /**
     * 
     */
    public function handle()
    {
        if (!$this->model instanceof Prospect) {
            return;
        }
        
        if (!$this->action->value) {
            return;
        }
        
        if (!isset($this->action->value['message']) || !$this->action->value['message']) {
            return;
        }

        if (!isset($this->action->value['source']) || !$this->action->value['source']) {
            return;
        }

        $this->model
            ->sms()
            ->create([
                'message' => $this->action->value['message'], 
                'source' => $this->action->value['source'],
                'from_user' => 1,
            ]);
    }
}