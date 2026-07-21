<?php

namespace App\Jobs\SMS;

use App\Models\Sms;
use App\Utils\ProjectSetting;
use App\Services\SMS\Brevo as BrevoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class Brevo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Sms $sms;

    /**
     * Create a new job instance.
     */
    public function __construct(Sms $sms)
    {
        $this->sms = $sms;
    }

    /**
     * Execute the job.
     */
    public function handle(BrevoService $brevo): void
    {
        try {
            $this->setSettings();
            $brevo->sendSMS($this->sms->prospect->mobile_phone_number, $this->sms->message);
            $this->sms->update(['sent_at' => \Carbon\Carbon::now()]);
        } catch (\Exception $e) {
            $this->sms->update(['error' => $e->getMessage()]);
            \ProjectLog::error($this->sms->prospect->project, "Brevo: " . $e->getMessage());
        }
    }

    /**
     * Set brevo settings
     * from the project sms settings
     */
    protected function setSettings()
    {
        $project = $this->sms->prospect->project;
        $settings = ProjectSetting::get($project, "brevo", null);

        if (!$settings) {
            return;
        }

        Config::set([
            'brevo.api_uri' => data_get($settings, 'api_uri', "https://api.brevo.com/v3/transactionalSMS/send"),
            'brevo.api_key' => data_get($settings, 'api_key', null),
            'brevo.sender' => data_get($settings, 'sender', null),
        ]);
    }
}