<?php

namespace App\Notifications;

use App\Models\File;
use App\Models\Prospect;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class GoogleContactDeletedProspect extends Notification implements ShouldQueue
{
    use Queueable;

    protected $prospect;

    /**
     * Create a new notification instance.
     */
    public function __construct(Prospect $prospect)
    {
        $this->prospect = $prospect;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [\App\Channels\GoogleContactDeletedProspectChannel::class];
    }

    /**
     * 
     */
    public function getProspect()
    {
        return $this->prospect;
    }
    
}
