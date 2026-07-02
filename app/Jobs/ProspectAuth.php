<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\Prospect;
use App\Notifications\ProspectAuth as NotificationsProspectAuth;
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
        if (!($settings = ProjectSetting::get($this->prospect->project, "email"))) {
            ProjectLog::error($this->prospect->project, trans('email.error.empty_setting'));
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
            $this->prospect->notify(new NotificationsProspectAuth($this->prospect));
        } catch (\Exception $e) {
        }
    }
}
