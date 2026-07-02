<?php

namespace App\Jobs\SMS;

use App\Models\Prospect;
use App\Models\Sms;
use App\Utils\ProjectSetting;
use App\Services\SMS\SMSBOX as SMSBOXService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class SMSBOX implements ShouldQueue
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
    public function handle(SMSBOXService $smsbox): void
    {
        try {
            $this->setSettings();
            $smsbox->sendSMS($this->sms->prospect->mobile_phone_number, $this->sms->message);
            $this->sms->update(['sent_at' => \Carbon\Carbon::now()]);
        } catch (\Exception $e) {
            $this->sms->update(['error' => $e->getMessage()]);
            \ProjectLog::error($this->sms->prospect->project, "SMSBOX: " . $e->getMessage());
        }
    }
    
    /**
     * Set smsbox settings
     * from the message project email settings
     */
    protected function setSettings()
    {
        
        $project = $this->sms->prospect->project;
        $settings = ProjectSetting::get($project, "smsbox", null);

        if (!$settings) {
            return;
        }

        Config::set([
            'smsbox.api_uri' => data_get($settings, 'api_uri', "https://api.smsbox.pro/1.1/api.php"),
            'smsbox.hlr_uri' => data_get($settings, 'hlr_uri', "https://api.smsbox.net/hlr/json/send"),
            'smsbox.api_key' => data_get($settings, 'api_key', null),
        ]);
    }
}
