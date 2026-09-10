<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\Prospect;
use App\Notifications\ProspectAuth as NotificationsProspectAuth;
use App\Notifications\ThreadMessage;
use App\Utils\ProjectLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class ProspectAuth implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $prospect;

    /**
     * Create a new job instance.
     */
    public function __construct(Prospect $prospect)
    {
        $this->prospect = $prospect;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!ProjectMail::configure($this->prospect->project)) {
            ProjectLog::error($this->prospect->project, trans('email.error.empty_setting'));
            return;
        }

        try {
            $this->prospect->notify(new NotificationsProspectAuth($this->prospect));
        } catch (\Exception $e) {
        }
    }
}
