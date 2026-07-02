<?php

namespace App\Channels;

use App\Jobs\SMS\MTarget;
use Illuminate\Notifications\Notification;

class MTargetChannel
{
    public function send($notifiable, Notification $notification)
    {
        $sms = $notification->toSms($notifiable);
        MTarget::dispatch($sms)->onQueue('sms');
    }
}
