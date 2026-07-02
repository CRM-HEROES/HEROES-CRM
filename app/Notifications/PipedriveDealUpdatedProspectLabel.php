<?php

namespace App\Notifications;

use App\Models\Label;
use App\Models\Prospect;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PipedriveDealUpdatedProspectLabel extends Notification implements ShouldQueue
{
    use Queueable;

    protected Prospect $prospect;
    protected Label $label;

    /**
     * Create a new notification instance.
     */
    public function __construct(Prospect $prospect, Label $label)
    {
        $this->prospect = $prospect;
        $this->label = $label;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [\App\Channels\PipedriveDealUpdatedProspectLabelChannel::class];
    }

    /**
     * 
     */
    public function getProspect()
    {
        return $this->prospect;
    }
    
    /**
     * 
     */
    public function getLabel()
    {
        return $this->label;
    }
    
}
