<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\WorkflowExecution;
use App\Workflows\WorkflowMonitor;
use Illuminate\Http\Request;

/**
 * Follow-up of the workflow executions: what ran, what waits, what failed,
 * and why.
 *
 * Permissions (on the project):
 *   projectWorkflowExecutionView  see the executions, their steps, the figures
 *   projectWorkflowExecute        cancel an execution in progress
 *
 * Every query is bound to the project of the URL. What is read for a list is
 * bounded (pagination, sampling) and does not depend on the number of rows
 * shown: no query per execution.
 */
class WorkflowExecutionController extends Controller
{
    public function __construct(protected WorkflowMonitor $monitor)
    {
    }

    /**
     * Executions of the project, most recent first
     *
     * Filters: workflow (id), status (one or several, comma separated),
     * subject_type + subject_id (the history of one prospect / order / appointment)
     */
    public function index(Request $request, Project $project)
    {
        $this->authorizeAction($project, 'ExecutionView');

        $data = $request->validate([
            'workflow' => 'nullable|integer',
            'status' => 'nullable|string|max:100',
            'subject_type' => 'nullable|in:prospect,order,event',
            'subject_id' => 'nullable|integer',
            'per_page' => 'nullable|integer|min:5|max:100',
        ]);

        return $this->monitor->executions($project->id, $data, (int) ($data['per_page'] ?? 25));
    }

    /**
     * An execution and its steps, in the order they ran
     */
    public function show(Project $project, WorkflowExecution $execution)
    {
        $this->authorizeAction($project, 'ExecutionView', $execution);

        return $this->monitor->detail($execution);
    }

    /**
     * Stop an execution that is not over. The steps already done stay done.
     */
    public function cancel(Project $project, WorkflowExecution $execution)
    {
        $this->authorizeAction($project, 'Execute', $execution);

        $cancelled = WorkflowExecution::query()
            ->whereKey($execution->id)
            ->whereIn('status', [WorkflowExecution::PENDING, WorkflowExecution::WAITING])
            ->update([
                'status' => WorkflowExecution::CANCELLED,
                'error' => 'Cancelled by a user.',
                'finished_at' => now(),
                'resume_at' => null,
                'waiting_event' => null,
                'updated_at' => now(),
            ]);

        // A running execution is being handled by a worker right now
        abort_unless($cancelled, 422, "Cette exécution ne peut plus être annulée : elle est en cours de traitement ou déjà terminée.");

        return $this->monitor->detail($execution->fresh());
    }

    /**
     * Figures of the project over the last days
     */
    public function stats(Request $request, Project $project)
    {
        $this->authorizeAction($project, 'ExecutionView');

        $data = $request->validate(['days' => 'nullable|integer|min:1|max:90']);

        return $this->monitor->stats($project->id, (int) ($data['days'] ?? 7));
    }

    /**
     * The workflows a prospect went through (the history shown on their page)
     */
    public function prospect(Project $project, Prospect $prospect)
    {
        $this->authorizeAction($project, 'ExecutionView');

        // The prospect is looked up with the usual scope: one the user cannot see is a 404
        abort_unless($prospect->project_id == $project->id, 404);

        return $this->monitor->prospectHistory($project->id, $prospect->id);
    }

    protected function authorizeAction(Project $project, string $action, ?WorkflowExecution $execution = null): void
    {
        abort_unless(auth()->user()->can("projectWorkflow$action", $project), 404);

        if ($execution) {
            abort_unless($execution->project_id == $project->id, 404);
        }
    }
}
