<?php

namespace App\Jobs\Pipedrive;

use App\Models\PipedriveAccount;
use App\Services\Pipedrive;
use App\Utils\ProjectSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PersonShow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected PipedriveAccount $pipedriveAccount;
    protected $id;

    /**
     * Create a new job instance.
     */
    public function __construct(PipedriveAccount $pipedriveAccount, $id)
    {
        $this->pipedriveAccount = $pipedriveAccount;
        $this->id = $id;
    }

    /**
     * Execute the job.
     */
    public function handle(Pipedrive $pipedrive): void
    {
        // Avoid duplicating person
        if ($this->getMappedProspect($this->id)) {
            return;
        }

        $id = $this->createProspect();
        $this->mapProspectWithPerson($id, $this->id);
        
        $this->setSettings();
        
        $fields = ProjectSetting::get($this->pipedriveAccount->project, 'pipedrive.person.fields', []);
        $response = $pipedrive->showPerson($this->id);

        if ($response && $response['success']) {
            $person = $response['data'];
            $prospect = $this->mapPerson($person, $fields);
            $this->updateProspect($id, $prospect);
        } else {

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
     * Check mapped prospect
     */
    protected function getMappedProspect($person) {
        return DB::table('pipedrive_person')
            ->where('person_id', $person)
            ->where('pipedrive_account_id', $this->pipedriveAccount->id)
            ->first(['prospect_id']);
    }

    /**
     * 
     */
    protected function mapProspectWithPerson($prospectId, $personId) {
        DB::table('pipedrive_person')->insert([
            'prospect_id' => $prospectId,
            'person_id' => $personId,
            'pipedrive_account_id' => $this->pipedriveAccount->id,
            'from_pipedrive' => 1,
        ]);
    }

    /**
     * Insert prospect into the DB
     */
    protected function createProspect() {
        $date = \Carbon\Carbon::now();

        return DB::table('prospects')->insertGetId([
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }

    /**
     * Update prospect
     */
    protected function updateProspect($id, &$prospect) {
        return DB::table('prospects')->where('id', $id)->limit(1)->update($prospect);
    }

    /**
     * Map person from Pipedrive
     * to a heroes prospect
     */
    protected function mapPerson(&$person, &$fields) {
        $prospect = [
            // Name
            'first_name'          => $person['first_name'],
            'last_name'           => $person['last_name'],
            'date_of_birth'       => $person['birthday'],

            // Contact
            'mobile_phone_number' => $this->getMobilePhoneNumber($person),
            'phone_number'        => $this->getPhoneNumber($person),
            'email'               => $person['primary_email'],
            
            // Address
            'latitude'            => $person['postal_address_lat'],
            'longitude'           => $person['postal_address_long'],
            'street'              => $this->getStreet($person),
            'postal_code'         => $person['postal_address_postal_code'],
            'country'             => $person['postal_address_country'],
            'state'               => $person['postal_address_admin_area_level_1'],
            'county'              => $person['postal_address_admin_area_level_2'],

            // Job
            'job_title'           => $person['job_title'],
            'company_name'        => $person['org_name'],
            
            // Timestamp
            'created_at'          => $person['add_time'],
            'updated_at'          => $person['update_time'],

            // Other
            'project_id'          => $this->pipedriveAccount->project->id,
            'meta'                => []
        ];

        foreach ($fields as $pipedriveField => $field) {
            if (!isset($person[$pipedriveField]) || !$person[$pipedriveField]) {
                continue;
            }
        
            if (Str::startsWith($field, 'meta->')) {
                $field = str_replace('meta->', '', $field);
                $prospect['meta'][$field] = $person[$pipedriveField];
            } else {
                $prospect[$field] = $person[$pipedriveField];
            }
        }

        $prospect['meta'] = json_encode($prospect['meta']);

        return $prospect;
    }

    /**
     * Get mobile phone number
     * from a person
     */
    protected function getMobilePhoneNumber($person) {
        if (!isset($person['phone'])) {
            return null;
        }

        // Get number from this list
        // that is not labeled as "home"
        foreach ($person['phone'] as $phoneNumber) {
            if (!isset($phoneNumber['label']) || $phoneNumber['label'] != 'home') {
                return $phoneNumber['value'];
            }
        }

        return null;
    }

    /**
     * Get phone number
     * from a person
     */
    protected function getPhoneNumber($person) {
        if (!isset($person['phone'])) {
            return null;
        }

        // Get number from this list
        // that is labeled as "home"
        foreach ($person['phone'] as $phoneNumber) {
            if (isset($phoneNumber['label']) && $phoneNumber['label'] == 'home') {
                return $phoneNumber['value'];
            }
        }

        return null;
    }

    /**
     * Get street
     * from a person
     */
    protected function getStreet(&$person) {
        implode(
            ' ', 
            array_filter(array_map(function ($address) use($person) {
                return isset($person[$address]) ? $person[$address] : null;
            }, [
                'postal_address_street_number', 
                'postal_address_route', 
                'postal_address_sublocality', 
                'postal_address_locality'
            ]))
        );
    }
}
