<?php

namespace App\Observers;

use App\Models\Sms;
use App\Utils\Field\Renderer\ProjectFieldRenderer;
use App\Utils\Field\Renderer\ProspectFieldRenderer;
use App\Utils\ProjectSetting;

class SmsObserver
{
    /**
     * Handle the Sms "creating" event.
     */
    public function creating(Sms $sms): void
    {
        $sms->message = $this->computeMessage($sms);
    }

    /**
     * Handle the Sms "created" event.
     */
    public function created(Sms $sms): void
    {
        $this->send($sms);
    }

    /**
     * Send an sms according to the source
     */
    protected function send(Sms $sms): void
    {
        switch ($sms->source) {
            case 'smsbox':
                $this->sendBySMSBOX($sms);
                break;
                
            case 'ultramsg':
                $this->sendByUltramsg($sms);
                break;
    
            case 'mtarget':
                $this->sendByMTarget($sms);
                break;
    
            case 'ringover':
            case 'whatsapp':
            case 'telephone':
                break;
                
            default:
                $sms->update(['error' => "SMS: " . trans('sms.error.unknown_source', ['source' => $sms->source])]);
                \ProjectLog::error($sms->prospect->project, "SMS: " . trans('sms.error.unknown_source', ['source' => $sms->source]));
                break;
        }
    }

    /**
     * Send SMSBOX sms
     */
    protected function sendBySMSBOX(Sms $sms): void
    {
        if (!ProjectSetting::check($sms->prospect->project, "smsbox")) {
            $sms->update(['error' => "SMSBOX: " . trans('sms.smsbox.error.empty_setting')]);
            \ProjectLog::error($sms->prospect->project, "SMSBOX: " . trans('sms.smsbox.error.empty_setting'));
            return;
        }

        $sms->prospect->notify(new \App\Notifications\SMSBOX($sms));
    }

    /**
     * Send Ultramsg sms
     */
    protected function sendByUltramsg(Sms $sms): void
    {
        if (!ProjectSetting::check($sms->prospect->project, "ultramsg")) {
            $sms->update(['error' => "Ultramsg: " . trans('sms.ultramsg.error.empty_setting')]);
            \ProjectLog::error($sms->prospect->project, "Ultramsg: " . trans('sms.ultramsg.error.empty_setting'));
            return;
        }

        $sms->prospect->notify(new \App\Notifications\Ultramsg($sms));
    }
    
    /**
     * Send MTarget sms
     */
    protected function sendByMTarget(Sms $sms): void
    {
        if (!ProjectSetting::check($sms->prospect->project, "mtarget")) {
            $sms->update(['error' => "MTarget: " . trans('sms.mtarget.error.empty_setting')]);
            \ProjectLog::error($sms->prospect->project, "MTarget: " . trans('sms.mtarget.error.empty_setting'));
            return;
        }

        $sms->prospect->notify(new \App\Notifications\MTarget($sms));
    }
    
    /**
     * Compute sms message
     */
    protected function computeMessage(Sms $sms)
    {
        $renderers = [
            new ProspectFieldRenderer($sms->prospect->project, $sms->prospect),
            new ProjectFieldRenderer($sms->prospect->project)
        ];

        return array_reduce($renderers, function($carry, $renderer) {
            return $renderer->render($carry);
        }, $sms->message);
    }
}
