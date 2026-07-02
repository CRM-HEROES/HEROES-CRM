<?php

namespace App\Jobs\Pipedrive;

use App\Models\PipedriveAccount;
use App\Models\Project;
use App\Models\Prospect;
use App\Services\Pipedrive;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class PersonAdd implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected PipedriveAccount $pipedriveAccount;
    protected Prospect $prospect;

    /**
     * Create a new job instance.
     */
    public function __construct(PipedriveAccount $pipedriveAccount, Prospect $prospect)
    {
        $this->pipedriveAccount = $pipedriveAccount;
        $this->prospect = $prospect;
    }

    /**
     * Execute the job.
     */
    public function handle(Pipedrive $pipedrive): void
    {
        // Avoid duplicating person
        if ($this->getMappedPerson($this->prospect)) {
            return;
        }

        $this->setSettings();
        
        $person = $this->mapProspect($this->prospect);
        $response = $pipedrive->storePerson($person);

        if ($response && $response['success']) {
            $person = $response['data'];
            $this->mapProspectWithPerson($this->prospect->id, $person['id']);
        }
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
     * Chaeck mapped prospect
     */
    protected function getMappedPerson($prospect) {
        return DB::table('pipedrive_person')
            ->where('prospect_id', $prospect)
            ->where('pipedrive_account_id', $this->pipedriveAccount->id)
            ->first(['prospect_id']);
    }

    /**
     * Map heroes prospect
     * to a Pipedrive person
     */
    protected function mapProspect($prospect) {
        $person = ['name' => $prospect->full_name];

        foreach ([
            'latitude'     => 'postal_address_lat',
            'longitude'    => 'postal_address_long',
            'street'       => 'postal_address_route',
            'postal_code'  => 'postal_address_postal_code',
            'country'      => 'postal_address_country',
            'state'        => 'postal_address_admin_area_level_1',
            'county'       => 'postal_address_admin_area_level_2',
            'job_title'    => 'job_title',
            'company_name' => 'org_name',
        ] as $key => $value) {
            if ($prospect->{$key}) {
                $person[$value] = $prospect->{$key};
            }
        }

        // Email
        if ($prospect->email) {
            $person['email'] = [[
                "value"   => $prospect->email,
                "primary" => "true",
                "label"   => "work"
            ]];
        }

        // Mobile phone number
        $person['phone'] = [];
        if ($prospect->mobile_phone_number) {
            $person['phone'][] = [
                "value"   => $prospect->mobile_phone_number,
                "primary" => "true",
                "label"   => "work"
            ];
        }

        // Phone number
        if ($prospect->phone_number) {
            $person['phone'][] = [
                "value"   => $prospect->phone_number,
                "primary" => count($person['phone']) == 0,
                "label"   => "home"
            ];
        }

        if (!$person['name']) {
            $person['name'] = '[HeroesCRM]';
        }

        return $person;
    }

    /**
     * 
     */
    protected function mapProspectWithPerson($prospectId, $personId) {
        DB::table('pipedrive_person')
            ->updateOrInsert(
                [
                    'prospect_id' => $prospectId,
                    'person_id' => $personId,
                    'pipedrive_account_id' => $this->pipedriveAccount->id,
                ],
                ['from_pipedrive' => 0]
            );
    }

}
