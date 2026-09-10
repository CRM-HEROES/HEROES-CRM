<?php

namespace App\Jobs;

use App\Models\Message;
use App\Notifications\ThreadMessage;
use App\Utils\ProjectLog;
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
        if (!ProjectMail::configure($this->message->thread->project)) {
            $this->message->update([
                'error' => trans('email.error.empty_setting')
            ]);
            ProjectLog::error($this->message->thread->project, trans('email.error.empty_setting'));
            return;
        }

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
