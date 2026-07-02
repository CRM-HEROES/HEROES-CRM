<?php

namespace App\Notifications;

use App\Models\Import;
use App\Utils\Project\LogoThumbnail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportFinished extends Notification
{
    use Queueable;

    protected $import;

    /**
     * Create a new notification instance.
     */
    public function __construct(Import $import)
    {
        $this->import = $import;
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
                    ->subject("Import terminé")
                    ->from(config("mail.from.address"), $this->import->project->name)
                    ->view('emails.import-finished', [
                        'icon' => $this->icon(),
                        'title' => $this->import->project->name,
                        'import' => $this->import,
                        'notifiable' => $notifiable
                    ]);

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
        $thumbnail = (new LogoThumbnail($this->import->project, 40))->generate();

        if ($thumbnail) {
            return "data:image/jpeg;base64," . base64_encode($thumbnail);
        } else {
            return "";
        }
    }
}
