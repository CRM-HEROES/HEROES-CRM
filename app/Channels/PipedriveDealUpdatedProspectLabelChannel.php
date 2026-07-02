<?php

namespace App\Channels;

use App\Jobs\Pipedrive\DealStageUpdate;
use App\Models\PipedriveAccount;
use Illuminate\Notifications\Notification;

class PipedriveDealUpdatedProspectLabelChannel
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
        $label = $notification->getLabel();
        DealStageUpdate::dispatchAfterResponse($account, $prospect, $label)->onQueue('imports');
    }
}
