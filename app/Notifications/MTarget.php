<?php

namespace App\Notifications;

use App\Models\Sms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MTarget extends Notification implements ShouldQueue
{
    use Queueable;

    protected $sms;

    /**
     * Create a new notification instance.
     */
    public function __construct(Sms $sms)
    {
        $this->sms = $sms;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [\App\Channels\MTargetChannel::class];
    }

    /**
     * 
     */
    public function toSms($notifiable)
    {
        return $this->sms;
    }
    
}
