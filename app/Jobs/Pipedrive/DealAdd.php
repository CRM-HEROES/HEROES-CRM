<?php

namespace App\Jobs\Pipedrive;

use App\Models\Label;
use App\Models\PipedriveAccount;
use App\Models\Project;
use App\Models\Prospect;
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

class DealAdd implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected PipedriveAccount $pipedriveAccount;
    protected Prospect $prospect;
    protected Label $label;

    /**
     * Create a new job instance.
     */
    public function __construct(PipedriveAccount $pipedriveAccount, Prospect $prospect, Label $label)
    {
        $this->pipedriveAccount = $pipedriveAccount;
        $this->prospect = $prospect;
        $this->label = $label;
    }

    /**
     * Execute the job.
     */
    public function handle(Pipedrive $pipedrive): void
    {
        // Create deal check
        if (!$this->checkNewDealLabel($this->prospect->project, $this->label)) {
            return;
        }

        $this->setSettings();
        
        // Check prospect associated person
        if (!($personId = $this->getMappedPerson($this->prospect))) {
            // Create person if doesn t exist
            PersonAdd::dispatch($this->pipedriveAccount, $this->prospect)->onConnection('sync');
            $personId = $this->getMappedPerson($this->prospect);
        }
        
        // Avoid duplicating deal
        if ($this->getMappedDeal($pipedrive, $personId->person_id)) {
            return;
        }

        // Create deal from prospect
        // and mapped fields
        $fields = ProjectSetting::get($this->pipedriveAccount->project, 'pipedrive.person.fields', []);
        $deal = $this->mapDeal($this->prospect, $fields);

        // Ass
        $deal['person_id'] = $personId->person_id;

        // Store deal into Pipedrive account
        $response = $pipedrive->storeDeal($deal);
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
    protected function getMappedPerson($prospect) {
        return DB::table('pipedrive_person')
            ->where('prospect_id', $prospect->id)
            ->where('pipedrive_account_id', $this->pipedriveAccount->id)
            ->first(['person_id']);
    }

    /**
     * Check mapped prospect
     */
    protected function getMappedDeal(Pipedrive $pipedrive, $personId) {
        $response = $pipedrive->getPersonDeals($personId);

        if (!$response || !$response['success']) {
            return;
        }

        $deals = is_array($response['data']) ? $response['data'] : [];

        return count($deals) > 0 ? $deals[0] : null;
    }

    /**
     * Map heroes prospect
     * to a Pipedrive deal
     */
    protected function mapDeal($prospect, $fields) {
        $deal = ['title' => $prospect->full_name];

        foreach ($fields as $pipedriveField => $field) {
            if (Str::startsWith($field, 'meta->')) {
                $field = str_replace('meta->', '', $field);
                $deal[$pipedriveField] = $prospect['meta'][$field];
            } else {
                $deal[$pipedriveField] = $prospect[$field];
            }
        }

        if (!$deal['title']) {
            $deal['title'] = '[HeroesCRM]';
        }

        return $deal;
    }

    /**
     * 
     */
    protected function mapProspectWithPerson($prospectId, $personId) {
        DB::table('pipedrive_person')->insert([
            'prospect_id' => $prospectId,
            'person_id' => $personId,
            'pipedrive_account_id' => $this->pipedriveAccount->id,
            'from_pipedrive' => 0,
        ]);
    }

    /**
     * 
     */
    protected function checkNewDealLabel(Project $project, Label $label) {
        $labels = (array) ProjectSetting::get($project, 'pipedrive.deal.store.labels', []);

        return array_search($label->id, $labels) !== FALSE;
    }

}
