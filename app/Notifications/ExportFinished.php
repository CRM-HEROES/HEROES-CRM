<?php

namespace App\Notifications;

use App\Models\Export;
use App\Utils\Project\LogoThumbnail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ExportFinished extends Mailable/*Notification implements ShouldQueue*/
{
    use Queueable;

    private $export;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Export $export)
    {
        $this->export = $export;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mailMessage = $this
                    ->subject("Export terminé.")
                    ->from(env('MAIL_USERNAME', 'crm.diris@gmail.com'), "Heroes CRM")
                    ->view('emails.export-finished', [
                        'icon' => $this->icon(),
                        'title' => $this->export->project->name,
                        'export' => $this->export,
                    ]);

        $i = 1;
        do {
        	$path = Storage::disk('exports')->path($this->export->id . '-' . $i . '.xlsx');
        	$mailMessage->attach($path);
        	++$i;
        } while (Storage::disk('exports')->exists($this->export->id . '-' . $i . '.xlsx'));

        return $mailMessage;
    }
    
    /**
     * 
     */
    protected function icon()
    {
        $thumbnail = (new LogoThumbnail($this->export->project, 40))->generate();

        if ($thumbnail) {
            return "data:image/jpeg;base64," . base64_encode($thumbnail);
        } else {
            return "";
        }
    }
}
