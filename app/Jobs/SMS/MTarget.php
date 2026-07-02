<?php

namespace App\Jobs\SMS;

use App\Models\Prospect;
use App\Models\Sms;
use App\Services\SMS\MTarget as MTargetService;
use App\Utils\ProjectSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class MTarget implements ShouldQueue
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
    public function handle(MTargetService $mtarget): void
    {
        try {
            $this->setSettings();
            
            $mtarget->sendSMS($this->sms->prospect->mobile_phone_number, $this->sms->message);
            $this->sms->update(['sent_at' => \Carbon\Carbon::now()]);
        } catch (\Exception $e) {
            $this->sms->update(['error' => $e->getMessage()]);
            \ProjectLog::error($this->sms->prospect->project, "MTarget: " . $e->getMessage());
        }
    }
    
    /**
     * Set mtarget sms settings
     * from the mtarget project sms settings
     */
    protected function setSettings()
    {
        
        $project = $this->sms->prospect->project;
        $settings = ProjectSetting::get($project, "mtarget", null);

        if (!$settings) {
            return;
        }

        Config::set([
            'mtarget.api_uri' => data_get($settings, 'api_uri', "https://api-public-2.mtarget.fr"),
            'mtarget.username' => data_get($settings, 'username', null),
            'mtarget.password' => data_get($settings, 'password', null),
        ]);
    }
}
