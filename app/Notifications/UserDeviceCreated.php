<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserDeviceCreated extends Notification
{
    use Queueable;

    private $user;
    private $device;
    private $session;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, $device, $session)
    {
        $this->user = $user;
        $this->device = $device;
        $this->session = $session;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject("Nouvel appareil de connexion.")
                    ->view('emails.user-device-created', [
                        'user' => $this->user,
                        'device' => $this->device,
                        'session' => $this->session,
                    ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
