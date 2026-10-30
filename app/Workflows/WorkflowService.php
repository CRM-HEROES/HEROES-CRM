<?php

namespace App\Workflows;

use App\Jobs\Workflow\BaselineFilterMemberships;
use App\Models\Workflow;
use App\Models\WorkflowVersion;
use Illuminate\Support\Facades\DB;

/**
 * Lifecycle of a workflow: versions and status.
 *
 * Statuses
 *  - draft:    never starts anything and never advances anything.
 *  - active:   starts executions on its trigger.
 *  - paused:   no new execution. The executions already in progress are HELD
 *              (they keep their state, no node runs) and carry on when the
 *              workflow is activated again.
 *  - archived: no new execution, and the executions in progress are cancelled.
 *
 * Versions
 *  A version is immutable. Publishing creates the next one and makes it the
 *  version of the NEW executions; the executions already started keep running
 *  on the version they were started with.
 */
class WorkflowService
{
    public function __construct(
        protected WorkflowValidator $validator,
        protected WorkflowExecutionManager $executions,
    ) {
    }

    /**
     * Publish a graph as the next version of the workflow.
     *
     * @throws WorkflowValidationException
     */
    public function publish(Workflow $workflow, array $graph, ?int $userId = null): WorkflowVersion
    {
        $this->assertValid($graph, $workflow->project_id);

        $trigger = (new WorkflowGraph($graph))->triggerNode();
        $isFilterTrigger = str_contains($trigger['config']['trigger'], '.filter_');

        $version = DB::transaction(function () use ($workflow, $graph, $trigger, $userId, $isFilterTrigger) {
            // Serializes concurrent publications of the same workflow
            Workflow::query()->whereKey($workflow->id)->lockForUpdate()->first(['id']);

            $version = WorkflowVersion::create([
                'workflow_id' => $workflow->id,
                'version' => (int) WorkflowVersion::where('workflow_id', $workflow->id)->max('version') + 1,
                'graph' => $graph,
                'trigger_type' => $trigger['config']['trigger'],
                // Only the filter triggers have a state to initialise
                'baselined_at' => $isFilterTrigger ? null : now(),
                'creator_id' => $userId,
            ]);

            $workflow->update(['active_version_id' => $version->id]);

            if ($isFilterTrigger) {
                // The memberships belong to the filter of the previous version
                DB::table(FilterTransitionDetector::TABLE)->where('workflow_id', $workflow->id)->delete();
            }

            return $version;
        });

        if ($isFilterTrigger && $workflow->status === Workflow::ACTIVE) {
            BaselineFilterMemberships::dispatch($workflow->id, $version->id);
        }

        return $version;
    }

    /**
     * @throws WorkflowValidationException
     */
    public function activate(Workflow $workflow): void
    {
        $version = $workflow->activeVersion;

        if (!$version) {
            throw new WorkflowValidationException(['Publiez le workflow avant de l\'activer.']);
        }

        // The environment may have changed since the publication
        $this->assertValid($version->graph, $workflow->project_id);

        // Filter triggers: the subjects that changed while the workflow was
        // not active were not seen. The state is rebuilt from the current
        // data (nothing fires), and the trigger stays off until it is done.
        $isFilterTrigger = str_contains($version->trigger_type, '.filter_');

        if ($isFilterTrigger) {
            $version->update(['baselined_at' => null]);
            DB::table(FilterTransitionDetector::TABLE)->where('workflow_id', $workflow->id)->delete();
        }

        $workflow->update(['status' => Workflow::ACTIVE]);

        if ($isFilterTrigger) {
            BaselineFilterMemberships::dispatch($workflow->id, $version->id);
        }
    }

    public function pause(Workflow $workflow): void
    {
        $workflow->update(['status' => Workflow::PAUSED]);
    }

    public function archive(Workflow $workflow): void
    {
        $workflow->update(['status' => Workflow::ARCHIVED]);
        $this->executions->cancelAll($workflow->id, 'The workflow was archived.');
    }

    /**
     * @throws WorkflowValidationException
     */
    protected function assertValid(array $graph, int $projectId): void
    {
        $errors = $this->validator->validate($graph, $projectId);

        if ($errors) {
            throw new WorkflowValidationException($errors);
        }
    }
}
