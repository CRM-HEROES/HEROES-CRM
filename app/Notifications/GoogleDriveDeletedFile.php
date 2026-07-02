<?php

namespace App\Notifications;

use App\Models\File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class GoogleDriveDeletedFile extends Notification implements ShouldQueue
{
    use Queueable;

    protected $file;

    /**
     * Create a new notification instance.
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [\App\Channels\GoogleDriveDeletedFileChannel::class];
    }

    /**
     * 
     */
    public function getFile()
    {
        return $this->file;
    }
    
}
