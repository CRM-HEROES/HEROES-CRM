<?php

namespace App\Notifications;

use App\Models\Message;
use App\Utils\Project\LogoThumbnail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class UserThreadMessage extends Notification
{
    use Queueable;

    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
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
        $mailMessage = (new MailMessage)
                    ->subject("Ce message attend votre retour")
                    ->from(config("mail.from.address"), config("mail.from.name"))
                    ->view('emails.user-thread-message', [
                        'icon' => $this->icon(),
                        'title' => $this->message->thread->project->name,
                        'prospect' => $this->message->prospect,
                        'threadMessage' => $this->message->body,
                        'notifiable' => $notifiable
                    ]);

        if ($this->message->creator) {
            $mailMessage = $mailMessage->replyTo($this->message->creator->email, $this->message->creator->name);
        }

        foreach ($this->message->attachments as $attachment) {
            $mailMessage->attach(Storage::disk('messages')->path($attachment->path));
        }

        return $mailMessage;
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

    /**
     * 
     */
    protected function icon()
    {
        $thumbnail = (new LogoThumbnail($this->message->thread->project, 40))->generate();

        if ($thumbnail) {
            return "data:image/jpeg;base64," . base64_encode($thumbnail);
        } else {
            return "";
        }
    }
}
