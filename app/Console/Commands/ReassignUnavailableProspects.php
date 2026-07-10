<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\ProspectAutoAssignment;
use Illuminate\Console\Command;

class ReassignUnavailableProspects extends Command
{
    protected $signature = 'app:reassign-unavailable-prospects {project?}';
    protected $description = 'Reassign prospects assigned to unavailable users (on leave, absent, vacation) to available commercial agents.';

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

        $reassigned = $assignmentService->reassignUnavailableProspects($project);
        $this->info("Réaffectation terminée. {$reassigned} prospect(s) réaffecté(s).\n");

        return self::SUCCESS;
    }
}
