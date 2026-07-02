<?php

namespace App\Jobs\Pipedrive;

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

class DealsGet implements ShouldQueue
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
        $pipedriveDealStatusWon = ProjectSetting::get($this->project, 'pipedrive.deal.status.won');
        $pipedriveDealStatusLost = ProjectSetting::get($this->project, 'pipedrive.deal.status.lost');
        
        $take = 500;
        $skip = 0;

        // Chunk data
        do {
            $response = $pipedrive->getDeals(array_merge($this->filters, [
                'start' => $skip,
                'limit' => $take,
            ]));

            if (!$response || !$response['success']) {
                return;
            }
            
            foreach ($response['data'] as $deal) {
                // Associated prospect
                $prospect = $this->getProspect($deal);
                if (!$prospect) {
                    continue;
                }

                // Update
                $payload = $this->mapDeal($deal, $prospect, $fields);
                $this->updateProspect($prospect->id, $payload);

                // Won status
                if (isset($deal['status']) && $deal['status'] == "won" && $pipedriveDealStatusWon) {
                    $prospect->labels()->syncWithoutDetaching(intval($pipedriveDealStatusWon));
                }

                // Lost status
                if (isset($deal['status']) && $deal['status'] == "lost" && $pipedriveDealStatusLost) {
                    $prospect->labels()->syncWithoutDetaching(intval($pipedriveDealStatusLost));
                }
            }

            $skip += $take;
        } while (count($response['data']) >= $take);
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
     * Get deal associated prospect
     */
    protected function getProspect($deal)
    {
        if (!isset($deal['person_id']) || !$deal['person_id']) {
            return null;
        }

        if (!isset($deal['person_id']['value']) || !$deal['person_id']['value']) {
            return null;
        }

        $prospect = DB::table('pipedrive_person')
            ->where('person_id', $deal['person_id']['value'])
            ->where('pipedrive_account_id', $this->pipedriveAccount->id)
            ->first('prospect_id');

        if (!$prospect) {
            return null;
        }

        return Prospect::where('id', $prospect->prospect_id)->first(['id', 'meta']);
    }
    
    /**
     * Update prospect
     */
    protected function updateProspect($id, &$update) {
        DB::table('prospects')
            ->where('id', $id)
            ->limit(1)
            ->update($update);
    }
    
    /**
     * Map deal from Pipedrive
     * to a heroes prospect
     */
    protected function mapDeal(&$deal, &$p, &$fields) {
        $prospect = [
            'meta' => $p->meta ?: []
        ];

        foreach ($fields as $pipedriveField => $field) {
            if (!isset($deal[$pipedriveField]) || !$deal[$pipedriveField]) {
                continue;
            }
        
            // meta field
            if (Str::startsWith($field, 'meta->')) {
                $field = str_replace('meta->', '', $field);
                $prospect['meta'][$field] = $deal[$pipedriveField];
            // default field
            } else {
                $prospect[$field] = $deal[$pipedriveField];
            }
        }

        $prospect['meta'] = json_encode($prospect['meta']);

        return $prospect;
    }
}
