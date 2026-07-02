<?php

namespace App\Channels;

use App\Jobs\SMS\UltraMsg as UltraMsg;
use Illuminate\Notifications\Notification;

class UltramsgChannel
{
    public function send($notifiable, Notification $notification)
    {
        $sms = $notification->toSms($notifiable);
        UltraMsg::dispatch($sms)->onQueue('sms');
    }
}
