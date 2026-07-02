<?php

namespace App\Jobs\Google\Contact;

use App\Models\GoogleAccount;
use App\Models\Prospect;
use App\Services\Google\Contact as GoogleContact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ContactDelete implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected GoogleAccount $googleAccount;
    protected Prospect $prospect;

    /**
     * Create a new job instance.
     */
    public function __construct(GoogleAccount $googleAccount, Prospect $prospect)
    {
        $this->googleAccount = $googleAccount;
        $this->prospect = $prospect;
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleContact $googleContact): void
    {
        try {
            $googleContact->setAccessToken($this->googleAccount->token);
    
            if ($googleContact->isAccessTokenExpired()) {              
                $googleContact->fetchAccessTokenWithRefreshToken($googleContact->getRefreshToken());
                $token = $googleContact->getAccessToken();
                $googleContact->setAccessToken($token);    
                $this->googleAccount->update(['token' => $token]);
            }

			$service = new \Google_Service_PeopleService($googleContact);

            $contact = $this->googleAccount
                ->contacts()
                ->withPivot('google_contact_id')
                ->find($this->prospect->id);

            if (!$contact) {
                return;
            }

            $service->people->deleteContact($contact->pivot->google_contact_id);
            $this->googleAccount->contacts()->detach([$this->prospect->id]);
        } catch(\Exception $e) {
            throw $e;
        }
    }
}
