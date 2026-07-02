<?php

namespace App\Channels;

use App\Jobs\Pipedrive\PersonUpdate;
use App\Models\PipedriveAccount;
use Illuminate\Notifications\Notification;

class PipedrivePersonUpdatedProspectChannel
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
        PersonUpdate::dispatchAfterResponse($account, $prospect)->onQueue('imports');
    }
}
