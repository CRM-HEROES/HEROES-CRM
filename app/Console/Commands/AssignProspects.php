<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\ProspectAutoAssignment;
use Illuminate\Console\Command;

class AssignProspects extends Command
{
    protected $signature = 'app:assign-prospects {project?}';
    protected $description = 'Assign unassigned prospects to available project users using Anthropic.';

    public function handle(ProspectAutoAssignment $assignmentService)
    {
        $projectId = $this->argument('project');
        $project = null;

        if ($projectId) {
            $project = Project::find($projectId);
            if (!$project) {
                $this->error("Project not found: {$projectId}");
                return self::FAILURE;
            }
        }

        $assigned = $assignmentService->assignUnassignedProspects($project);
        $this->info("Affectation automatique terminée. {$assigned} prospect(s) affecté(s).\n");

        return self::SUCCESS;
    }
}
