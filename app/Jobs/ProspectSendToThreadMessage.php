<?php

namespace App\Jobs;

use App\Mail\ThreadMessageMail;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Utils\ProjectMail;
use Illuminate\Support\Facades\Mail;

class ProspectSendToThreadMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Message $message;

    /**
     * Create a new job instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!ProjectMail::configure($this->message->thread->project)) {
            \ProjectLog::error($this->message->thread->project, trans('email.error.empty_setting'));
            return;
        }

        try {
            Mail::to($this->message->thread->send_to)->send(new ThreadMessageMail($this->message));

            $this->message->update([
                'sent' => 1
            ]);
        } catch (\Exception $e) {
            $this->message->update([
                'sent' => 0,
                // 'error' => $e->getMessage(),
                'error' => "Erreur d'envoi de mail, vérifiez que vous avez bien renseigné les informations pour se connecter à votre serveur email pour le projet " . $this->message->thread->project->name . ".",
            ]);
        }
    }
}
