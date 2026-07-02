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

class PersonUpdate implements ShouldQueue
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
        $personId = $this->getProspectPersonId($this->prospect);
        if (!$personId) {
            PersonAdd::dispatch($this->pipedriveAccount, $this->prospect)->onQueue('imports');
            return;
        }

        $this->setSettings();
        
        // Get updated person data from prospect
        $person = $this->mapProspect($this->prospect);
        // Update person
        $pipedrive->updatePerson($personId, $person);
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
     * Check if the person is mapped as prospect
     */
    protected function getProspectPersonId($prospect)
    {
        $person = DB::table('pipedrive_person')
            ->where('prospect_id', $prospect->id)
            ->where('pipedrive_account_id', $this->pipedriveAccount->id)
            ->first(['person_id']);

        return $person ? $person->person_id : null;
    }

    /**
     * Map heroes prospect
     * to a Pipedrive person
     */
    protected function mapProspect(&$prospect) {
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
            // Check dirty fields
            /*if (
                !$prospect->isDirty($key)
            ) {
                continue;
            }*/
            
            // Merge dirty field value into person
            $person[$value] = $prospect->{$key};
        }

        // Email
        if (/*$prospect->isDirty('email') && */$prospect->email) {
            $person['email'] = [[
                "value"   => $prospect->email,
                "primary" => "true",
                "label"   => "work"
            ]];
        }

        // Mobile phone number
        $person['phone'] = [];
        if (/*$prospect->isDirty('mobile_phone_number') && */$prospect->mobile_phone_number) {
            $person['phone'][] = [
                "value"   => $prospect->mobile_phone_number,
                "primary" => "true",
                "label"   => "work"
            ];
        }

        // Phone number
        if (/*$prospect->isDirty('phone_number') && */$prospect->phone_number) {
            $person['phone'][] = [
                "value"   => $prospect->phone_number,
                "primary" => count($person['phone']) == 0,
                "label"   => "home"
            ];
        }

        return $person;
    }

    /**
     * 
     */
    protected function mapProspectWithPerson($prospectId, $personId) {
        DB::table('pipedrive_person')->insert([
            'prospect_id' => $prospectId,
            'person_id' => $personId,
        ]);
    }

}
