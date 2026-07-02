<?php

namespace App\Jobs\SMS;

use App\Models\Prospect;
use App\Models\Sms;
use App\Services\SMS\Ultramsg as UltramsgService;
use App\Utils\ProjectSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class UltraMsg implements ShouldQueue
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
    public function handle(UltramsgService $ultraMsg): void
    {
        try {
            $this->setSettings();

            $ultraMsg->sendSMS($this->sms->prospect->mobile_phone_number, $this->sms->message);
            $this->sms->update(['sent_at' => \Carbon\Carbon::now()]);
        } catch (\Exception $e) {
            $this->sms->update(['error' => $e->getMessage()]);
            \ProjectLog::error($this->sms->prospect->project, "UltraMsg: " . $e->getMessage());
        }
    }
    
    /**
     * Set UltraMsg settings
     * from the message project email settings
     */
    protected function setSettings()
    {
        
        $project = $this->sms->prospect->project;
        
        $settings = ProjectSetting::get($project, "ultramsg", null);

        if (!$settings) {
            return;
        }

        Config::set([
            'ultramsg.api_uri' => data_get($settings, 'api_key', "https://api.ultramsg.com/"),
            'ultramsg.instance' => data_get($settings, 'instance', null),
            'ultramsg.token' => data_get($settings, 'token', null),
        ]); 
    }

}
