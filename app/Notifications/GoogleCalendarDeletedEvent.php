<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class GoogleCalendarDeletedEvent extends Notification implements ShouldQueue
{
    use Queueable;

    protected $event;

    /**
     * Create a new notification instance.
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [\App\Channels\GoogleCalendarDeletedEventChannel::class];
    }

    /**
     * 
     */
    public function getEvent()
    {
        return $this->event;
    }
    
}
