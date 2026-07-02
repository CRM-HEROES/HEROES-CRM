<?php

namespace App\Jobs\Pipedrive;

use App\Models\Label;
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
use Illuminate\Support\Str;

class StageShow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected PipedriveAccount $pipedriveAccount;
    protected $pipelineId;
    protected $id;

    /**
     * Create a new job instance.
     */
    public function __construct(PipedriveAccount $pipedriveAccount, $pipelineId, $id)
    {
        $this->pipedriveAccount = $pipedriveAccount;
        $this->pipelineId = $pipelineId;
        $this->id = $id;
    }

    /**
     * Execute the job.
     */
    public function handle(Pipedrive $pipedrive): void
    {
        $project = $this->pipedriveAccount->project;
        
        // Pipedrive labels category
        $categories = (array) ProjectSetting::get($project, 'pipedrive.pipelines', []);

        if (
            !is_array($categories) || 
            !isset($categories[$this->pipelineId]) || 
            !($categoryId = $categories[$this->pipelineId]) || 
            !($category = $project->categories()->find($categoryId))
        ) {
            return;
        }

        // Get related stages to the pipeline
        $stages = (array) ProjectSetting::get($project, 'pipedrive.stages.' . $this->pipelineId, []);
        if (!is_array($stages)) {
            $stages = [];
        }

        // Avoid duplicating stage
        if ($this->getMappedLabel($this->id, $stages)) {
            return;
        }

        // Labels
        $labels = $this->getLabels($categoryId);
        
        // Stage info from pipedrive
        $this->setSettings();
        $response = $pipedrive->showStage($this->id);

        if ($response && $response['success']) {
            $stage = $response['data'];
            
            // Stage label with the same name
            if (!($label = $labels->where('name', Str::slug($stage['name'], ' '))->first())) {
                $label = $category->labels()->create(['name' => $stage['name']]);
            }

            // Update setting
            $stages[$this->id] = $label->id;
            ProjectSetting::set($project, 'pipedrive.stages.' . $this->pipelineId, $stages);
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
     * Check mapped label
     */
    protected function getMappedLabel($stageId, &$stages) {

        if (!isset($stages[$stageId])) {
            return false;
        }

        $labelId = $stages[$stageId];
        if (!Label::find($labelId)) {
            return false;
        }

        return true;
    }

    /**
     * Get labels
     */
    protected function getLabels($categoryId) {
        $labels = Label::where('category_id', $categoryId)->get(['id', 'name']);
        $labels = $labels->map(function($label) {
            $label->name = Str::slug($label->name, ' ');

            return $label;
        });

        return $labels;
    }
}
