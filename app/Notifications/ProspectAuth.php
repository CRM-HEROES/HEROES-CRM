<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\Prospect;
use App\Utils\Project\LogoThumbnail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use MagicLink\Actions\LoginAction;
use MagicLink\MagicLink;

class ProspectAuth extends Notification
{
    use Queueable;

    public $prospect;

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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = MagicLink::create(
            new LoginAction($this->prospect, redirect()->route('client.project.home', $this->prospect->project), config('prospect.guard'))
        )->url;

        $mailMessage = (new MailMessage)
                    ->from("crm.diris@gmail.com", "Heroes CRM")
					->subject("Lien de connexion")
					->view('client.emails.prospect-auth', [
						'prospect' => $this->prospect,
						'url' => $url
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
        $thumbnail = (new LogoThumbnail($this->message->thread->project, 40))->generate();

        if ($thumbnail) {
            return "data:image/jpeg;base64," . base64_encode($thumbnail);
        } else {
            return "";
        }
    }
}
