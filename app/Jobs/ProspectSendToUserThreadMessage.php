<?php

namespace App\Jobs;

use App\Models\Message;
use App\Notifications\UserThreadMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Utils\ProjectMail;

class ProspectSendToUserThreadMessage implements ShouldQueue
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
            $this->message->thread->user->notify(new UserThreadMessage($this->message));

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
