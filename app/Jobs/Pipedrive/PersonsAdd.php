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

class PersonsAdd implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected PipedriveAccount $pipedriveAccount;
    protected $prospects;

    /**
     * Create a new job instance.
     */
    public function __construct(PipedriveAccount $pipedriveAccount, &$prospects)
    {
        $this->pipedriveAccount = $pipedriveAccount;
        $this->prospects = $prospects;
    }

    /**
     * Execute the job.
     */
    public function handle(Pipedrive $pipedrive): void
    {
        $this->setSettings();
        
        $prospects = $this->getProspects($this->prospects);

        foreach ($prospects as $prospect) {
            $person = $this->mapProspect($prospect);
            $response = $pipedrive->storePerson($person);

            if ($response && $response['success']) {
                $person = $response['data'];
                $this->mapProspectWithPerson($prospect->id, $person['id']);
            }
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
     * 
     */
    protected function getProspects(&$data) {
        return DB::table('prospects')
            ->leftJoin('pipedrive_person', function ($join) {
                $join->on('prospects.id', '=', 'pipedrive_person.prospect_id');
                $join->on('pipedrive_person.pipedrive_account_id', '=', DB::raw($this->pipedriveAccount->id));
            })
            ->whereNull('pipedrive_person.person_id')
            ->whereIn('prospects.id', $data)
            ->get([
                'prospects.id',

                'prospects.first_name', 
                '""', 
                
                'prospects.latitude', 
                'prospects.longitude', 
                'prospects.street', 
                'prospects.postal_code', 
                'prospects.country', 
                'prospects.state', 
                'prospects.county', 

                'prospects.job_title', 
                'prospects.company_name', 
                
                'prospects.email', 
                'prospects.mobile_phone_number', 
                'prospects.phone_number'
            ]);
    }

    /**
     * Map heroes prospect
     * to a Pipedrive person
     */
    protected function mapProspect($prospect) {
        $person = [
            'name' => implode(
                ' ', 
                array_filter(
                    array_map(
                        function($item) {
                            return trim($item);
                        }, [
                            $prospect->first_name,
                            $prospect->last_name,
                        ]
                    )
                )
            )
        ];

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
