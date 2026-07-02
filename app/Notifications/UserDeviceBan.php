<?php

namespace App\Notifications;

use App\Models\User;
use App\Utils\Tracker\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserDeviceBan extends Notification
{
    use Queueable;

    private $user;
    private $session;
    private $code;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, Session $session, $code)
    {
        $this->user = $user;
        $this->session = $session;
        $this->code = $code;
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
                    ->subject("Code de ban d'appareil connecté.")
                    ->view('emails.user-device-ban', [
                        'user' => $this->user,
                        'session' => $this->session,
                        'code' => $this->code,
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
