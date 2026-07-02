<?php

namespace App\Jobs;

use App\Models\Message;
use App\Notifications\ThreadMessage;
use App\Utils\ProjectLog;
use App\Utils\ProjectSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class ProspectThreadMessage implements ShouldQueue
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
        if (!($settings = ProjectSetting::get($this->message->thread->project, "email"))) {
            $this->message->update([
                'error' => trans('email.error.empty_setting')
            ]);
            ProjectLog::error($this->message->thread->project, trans('email.error.empty_setting'));
            return;
        }

        Config::set([
            'mail.mailers.smtp.transport'    => data_get($settings, 'driver', null),
            'mail.mailers.smtp.host'         => data_get($settings, 'host', null),
            'mail.mailers.smtp.port'         => data_get($settings, 'port', null),
            'mail.from.address'              => data_get($settings, 'from.address', null),
            'mail.from.name'                 => data_get($settings, 'from.name', null),
            'mail.mailers.smtp.encryption'   => data_get($settings, 'encryption', null),
            'mail.mailers.smtp.username'     => data_get($settings, 'username', null),
            'mail.mailers.smtp.password'     => data_get($settings, 'password', null), 
        ]);

        try {
            $this->message->prospect->notify(new ThreadMessage($this->message));

            $this->message->update([
                'sent' => 1
            ]);
        } catch (\Exception $e) {
            $this->message->update([
                'sent' => 0,
                'error' => $e->getMessage()
            ]);
        }
    }
}
