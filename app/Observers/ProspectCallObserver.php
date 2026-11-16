<?php

namespace App\Observers;

use App\Calls\CallSettings;
use App\Jobs\PrepareAutomaticCall;
use App\Models\Project;
use App\Models\Prospect;
use App\Workflows\WorkflowGraph;
use App\Workflows\WorkflowTriggerResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProspectCallObserver
{
    public function created(Prospect $prospect): void
    {
        if (!CallSettings::globalEnabled()) {
            return;
        }

        $project = Project::withoutGlobalScopes()->find($prospect->project_id);
        if ($project && $project->calls_enabled && $project->call_agent_id && !$this->hasAgentCallWorkflow($project->id)) {
            PrepareAutomaticCall::dispatch($prospect->id)->onQueue('agents');
        }
    }

    protected function hasAgentCallWorkflow(int $projectId): bool
    {
        if (!Schema::hasTable('workflows') || !Schema::hasTable('workflow_versions')) {
            return false;
        }

        $entries = app(WorkflowTriggerResolver::class)->entries($projectId, 'prospect.created');
        $versionIds = array_values(array_filter(array_column($entries, 'version_id')));
        if (!$versionIds) {
            return false;
        }

        foreach (DB::table('workflow_versions')->whereIn('id', $versionIds)->pluck('graph') as $storedGraph) {
            $graph = new WorkflowGraph(json_decode((string) $storedGraph, true) ?: []);
            $trigger = $graph->triggerNode();
            if (($trigger['config']['trigger'] ?? null) !== 'prospect.created') {
                continue;
            }

            $pending = [(string) $trigger['id']];
            $visited = [];
            while ($pending) {
                $nodeId = array_pop($pending);
                if (isset($visited[$nodeId])) {
                    continue;
                }
                $visited[$nodeId] = true;
                $node = $graph->node($nodeId);

                if (($node['type'] ?? null) === WorkflowGraph::ACTION && ($node['config']['action'] ?? null) === 'agent_call') {
                    return true;
                }

                foreach ($graph->edgesFrom($nodeId) as $edge) {
                    if (isset($edge['to'])) {
                        $pending[] = (string) $edge['to'];
                    }
                }
            }
        }

        return false;
    }
}
