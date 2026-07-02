<?php

namespace App\Jobs\Pipedrive;

use App\Models\PipedriveAccount;
use App\Services\Pipedrive;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class WebhookAdd implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected PipedriveAccount $pipedriveAccount;
    protected $name;
    protected $subscriptionUrl;
    protected $eventAction;
    protected $eventObject;

    /**
     * Create a new job instance.
     */
    public function __construct(
        PipedriveAccount $pipedriveAccount, 
        $name, 
        $subscriptionUrl, 
        $eventAction, 
        $eventObject
    )
    {
        $this->pipedriveAccount = $pipedriveAccount;
        $this->name = $name;
        $this->subscriptionUrl = $subscriptionUrl;
        $this->eventAction = $eventAction;
        $this->eventObject = $eventObject;
    }

    /**
     * Execute the job.
     */
    public function handle(Pipedrive $pipedrive): void
    {
        $this->setSettings();
        
        // Avoid duplicating
        // webhook
        if ($this->check($pipedrive)) {
            return;
        }

        $pipedrive->storeWebhook([
            'name'               => $this->name,
            'subscription_url'   => $this->subscriptionUrl,
            'event_action'       => $this->eventAction,
            'event_object'       => $this->eventObject,
            'http_auth_user'     => $this->pipedriveAccount->project->slug,
            'http_auth_password' => $this->pipedriveAccount->password,
        ]);
    }

    /**
     * Set Pipedrive settings
     * from the message project email settings
     */
    protected function setSettings()
    {
        Config::set([
            'pipedrive.api_token' => $this->pipedriveAccount->token,
        ]); 
    }

    /**
     * Check if an existing webhook
     * with the same 
     * action, object and subscription url 
     * already exists
     */
    protected function check(Pipedrive $pipedrive) {
        // Get all webhooks from pipedrive
        $webhooks = $pipedrive->getWebhooks();

        // Filter webhooks having the same
        // action, object and subscription url
        return count(array_filter($webhooks["data"], function($webhook) {
            return 
                $webhook['event_action'] == $this->eventAction && 
                $webhook['event_object'] == $this->eventObject && 
                $webhook['subscription_url'] == $this->subscriptionUrl;
        })) > 0;
    }
}
