<?php

namespace App\Notifications;

use App\Models\Message;
use App\Utils\Project\LogoThumbnail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ThreadMessage extends Notification
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
                    ->subject("Vous avez un nouveau message")
                    ->from(config("mail.from.address"), config("mail.from.name"))
                    ->view('emails.thread-message', [
                        'icon' => $this->icon(),
                        'title' => $this->message->thread->project->name,
                        'threadMessage' => $this->message->body,
                        'notifiable' => $notifiable
                    ]);

        if ($this->message->creator) {
            $mailMessage = $mailMessage->replyTo($this->message->creator->email, $this->message->creator->name);
        }

        foreach ($this->message->attachments as $attachment) {
            $mailMessage->attachData(file_get_contents(Storage::disk('messages')->path($attachment->path)), $attachment->name);
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
        return $this->message->thread->project->thumbnail;
        /*$thumbnail = (new LogoThumbnail($this->message->thread->project, 30))->generate();

        if ($thumbnail) {
            return "data:image/jpeg;base64," . base64_encode($thumbnail);
        } else {
            return "";
        }*/
    }
}
