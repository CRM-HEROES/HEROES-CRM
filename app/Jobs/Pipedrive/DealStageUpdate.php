<?php

namespace App\Jobs\Pipedrive;

use App\Models\Label;
use App\Models\PipedriveAccount;
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

class DealStageUpdate implements ShouldQueue
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
        $this->setSettings();
        
        $person = $this->getMappedPerson($this->prospect->id);
        if (!$person) {
            DealAdd::dispatchAfterResponse($this->pipedriveAccount, $this->prospect, $this->label)->onQueue('imports');
            return;
        }
        
        $response = $pipedrive->getPersonDeals($person->person_id);

        if (!$response || !$response['success']) {
            return;
        }

        $deals = is_array($response['data']) ? $response['data'] : [];
        if (count($deals) == 0) {
            DealAdd::dispatchAfterResponse($this->pipedriveAccount, $this->prospect, $this->label)->onQueue('imports');
            return;
        }

        // Get first deal
        $deal = $deals[count($deals) - 1];

        // Get mapped pipeline
        $project = $this->pipedriveAccount->project;
        $pipelineId = $this->getMappedPipeline($project, $this->label->category_id);
        if (!$pipelineId) {
            return;
        }

        // Get mapped stage
        $stageId = $this->getMappedStage($project, $pipelineId, $this->label->id);
        if (!$stageId) {
            return;
        }

        // Update deal
        $response = $pipedrive->updateDeal($deal['id'], ['pipeline_id' => $pipelineId, 'stage_id' => $stageId]);
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
     * Check mapped pipeline
     */
    protected function getMappedPipeline($project, $categoryId) {
        $categories = (array) ProjectSetting::get($project, 'pipedrive.pipelines', []);

        foreach ($categories as $pipelineId => $cId) {
            if ($cId == $categoryId) {
                return $pipelineId;
            }
        }

        return null;
    }

    /**
     * Check mapped stage
     */
    protected function getMappedStage($project, $pipelineId, $labelId) {
        $labels = (array) ProjectSetting::get($project, 'pipedrive.stages.' . $pipelineId, []);

        foreach ($labels as $stageId => $lId) {
            if ($lId == $labelId) {
                return $stageId;
            }
        }

        return null;
    }

    /**
     * Check mapped person
     */
    protected function getMappedPerson($prospect) {
        return DB::table('pipedrive_person')
            ->where('prospect_id', $prospect)
            ->where('pipedrive_account_id', $this->pipedriveAccount->id)
            ->first(['person_id']);
    }

}
