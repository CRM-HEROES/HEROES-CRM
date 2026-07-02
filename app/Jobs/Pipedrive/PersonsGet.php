<?php

namespace App\Jobs\Pipedrive;

use App\Models\PipedriveAccount;
use App\Models\Project;
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

class PersonsGet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected PipedriveAccount $pipedriveAccount;
    protected Project $project;
    protected $filters;

    /**
     * Create a new job instance.
     */
    public function __construct(PipedriveAccount $pipedriveAccount, Project $project, &$filters = [])
    {
        $this->pipedriveAccount = $pipedriveAccount;
        $this->project = $project;
        $this->filters = $filters;
    }

    /**
     * Execute the job.
     */
    public function handle(Pipedrive $pipedrive): void
    {
        $this->setSettings();

        $fields = ProjectSetting::get($this->project, 'pipedrive.fields', []);
        
        $take = 500;
        $skip = 0;

        // Chunk data
        do {
            $response = $pipedrive->getPersons(array_merge($this->filters, [
                'start' => $skip,
                'limit' => $take,
            ]));

            if (!$response || !$response['success']) {
                return;
            }

            $data = $this->removeExistingProspects($response['data']);
            $data = $this->associateExistingEmail($data);
            $prospects = $this->mapPersons($data, $fields);
            $prospectsIds = $this->createProspects($prospects);
            $personsIds = $this->getPersonsIds($data);
            $this->mapProspectsWithPersons($prospectsIds, $personsIds);

            $skip += $take;
        } while (count($data) >= $take);
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
     * Insert prospects into the DB
     */
    protected function createProspects(&$prospects) {
        $count = count($prospects);

        DB::table('prospects')->insert($prospects);

        // Return the ids of inserted prospects
        $data = DB::table('prospects')
            ->orderBy('id', 'desc')
            ->limit($count)
            ->get(['id'])
            ->pluck('id')
            ->toArray();

        return array_reverse($data);
    }

    /**
     * 
     */
    protected function mapProspectsWithPersons(&$prospectsIds, &$personsIds) {
        $data = array_map(function($prospectId, $personId) {
            return [
                'pipedrive_account_id' => $this->pipedriveAccount->id,
                'prospect_id' => $prospectId,
                'person_id' => $personId,
                'from_pipedrive' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ];
        }, $prospectsIds, $personsIds);

        DB::table('pipedrive_person')->insert($data);
    }

    /**
     * Get persons ids
     */
    protected function getPersonsIds(&$data) {
        // Persons ids
        return array_map(function($person) {
            return $person['id'];
        }, $data);
    }

    /**
     * Remove existing prospects
     * from the pipedrive persons list
     */
    protected function removeExistingProspects(&$data) {
        // Persons ids
        $personsIds = $this->getPersonsIds($data);

        // Existing persons
        $mapIds = DB::table('pipedrive_person')
            ->whereIn('person_id', $personsIds)
            ->where('pipedrive_account_id', $this->pipedriveAccount->id)
            ->get(['person_id'])
            ->pluck('person_id')
            ->toArray();

        // Filter
        return count($mapIds) > 0 ? array_filter($data, function($person) use($mapIds) {
            return array_search($person['id'], $mapIds) === FALSE;
        }) : $data;
    }

    /**
     * Remove existing prospects
     * from the pipedrive persons list
     */
    protected function associateExistingEmail(&$data) {
        // Persons emails
        $personsEmails = array_filter(
                array_map(function($person) {
                return $person['primary_email'];
            }, $data)
        );

        // Existing prospects having same email
        $prospects = DB::table('prospects')
            ->leftJoin('pipedrive_person', function ($join) {
                $join->on('prospects.id', '=', 'pipedrive_person.prospect_id');
                $join->on('pipedrive_person.pipedrive_account_id', '=', DB::raw($this->pipedriveAccount->id));
            })
            ->whereNull('pipedrive_person.person_id')
            ->whereIn('email', $personsEmails)
            ->where('project_id', $this->pipedriveAccount->project_id)
            ->whereNull('deleted_at')
            ->get(['id', 'email']);

        if ($prospects->count() == 0) {
            return $data;
        }

        $existingEmails = [];
        $map = $prospects->map(function($prospect) use($data, $existingEmails) {
            if (array_search($prospect->email, $existingEmails) !== FALSE) {
                return null;
            }

            $existingEmails[] = $prospect->email;

            // get person having same email
            $person = array_filter($data, function($d) use($prospect) {
                return $d['primary_email'] == $prospect->email;
            });
            $person = array_pop($person);

            return [
                'prospect_id'          => $prospect->id,
                'person_id'            => $person['id'],
                'pipedrive_account_id' => $this->pipedriveAccount->id,
                'from_pipedrive'       => 1
            ];
        })->filter()->toArray();

        DB::table('pipedrive_person')->insert($map);
    
        // Filter
        $mapIds = array_map(function($m) {
            return $m['person_id'];
        }, $map);

        return array_filter($data, function($person) use($mapIds) {
            return array_search($person['id'], $mapIds) === FALSE;
        });
    }

    /**
     * Map a list of persons to a list of prospects
     */
    protected function mapPersons(&$data, &$fields) {
        return array_map(function($person) use($fields) {
            return $this->mapPerson($person, $fields);
        }, $data);
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
            'project_id'          => $this->project->id,
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
