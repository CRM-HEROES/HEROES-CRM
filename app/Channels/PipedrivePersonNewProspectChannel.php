<?php

namespace App\Channels;

use App\Jobs\Pipedrive\PersonAdd;
use App\Models\PipedriveAccount;
use Illuminate\Notifications\Notification;

class PipedrivePersonNewProspectChannel
{
    public function send($notifiable, Notification $notification)
    {
        $account = PipedriveAccount::where([
            // Project
            'project_id' => $notifiable->id,
        ])
        ->first();

        if (!$account) {
            return;
        }
    
        $prospect = $notification->getProspect();
        PersonAdd::dispatchAfterResponse($account, $prospect)->onQueue('imports');
    }
}
