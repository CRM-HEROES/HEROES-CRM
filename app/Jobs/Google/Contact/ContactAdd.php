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

class ContactAdd implements ShouldQueue
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

            $person = new \Google_Service_PeopleService_Person([
                'names' => [
                    [
                        'givenName' => $this->prospect->first_name,
                        'familyName' => ""
                    ]
                ],
                'emailAddresses' => [
                    ['value' => $this->prospect->email]
                ],
                'phoneNumbers' => [
                    [
                        'value' => $this->prospect->phone_number,
                        'type' => 'home'
                    ], [
                        'value' => $this->prospect->mobile_phone_number,
                        'type' => 'mobile'
                    ],
                ]
            ]);

            $googleContact = $service->people->createContact($person);

            $this->googleAccount->contacts()->syncWithoutDetaching([
                $this->prospect->id => [
                    'google_contact_id' => $googleContact->resourceName
                ]
            ]);
        } catch(\Exception $e) {
            throw $e;
        }
    }
}
