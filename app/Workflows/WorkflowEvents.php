<?php

namespace App\Workflows;

use App\Jobs\Workflow\DispatchImportedProspectTriggers;
use App\Jobs\Workflow\EvaluateWorkflowTriggers;
use App\Jobs\Workflow\WakeWorkflowExecutions;
use App\Models\Calendar;
use App\Models\Prospect;
use Illuminate\Database\Eloquent\Model;

/**
 * Entry point for the CRM code that detects an event (model observers,
 * imports). Cheap and safe:
 *
 *  - when no active workflow concerns the project, it returns without any
 *    query (cached index) - this is the case of almost every event;
 *  - otherwise it only queues a job: the request does no workflow work;
 *  - it never throws: a problem with the workflows must not break the CRM.
 *
 * Nothing is recorded while a workflow action runs (see
 * WorkflowTriggerResolver::withoutTriggers).
 */
class WorkflowEvents
{
    /**
     * @param  string  $event  "created" | "updated"
     */
    public static function record(string $subjectType, Model $model, string $event): void
    {
        try {
            if (WorkflowTriggerResolver::suppressed()) {
                return;
            }

            $resolver = app(WorkflowTriggerResolver::class);

            // Nothing for this kind of subject in any project: stop here,
            // before even looking for the project of an order or appointment
            if (!$resolver->hasSubject($subjectType)) {
                return;
            }

            $projectId = self::projectId($subjectType, $model);

            if (!$projectId || !$resolver->hasSubject($subjectType, $projectId)) {
                return;
            }

            EvaluateWorkflowTriggers::dispatch(
                $subjectType,
                [$model->getKey()],
                $projectId,
                $event,
                $model->updated_at?->getTimestamp() ?? time()
            )->afterCommit();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Something happened to a prospect that a workflow may be waiting for
     * ("wait for an event" node): see WaitEventNode::EVENTS.
     */
    public static function awaited(string $event, ?int $prospectId): void
    {
        try {
            if (!$prospectId || WorkflowTriggerResolver::suppressed()) {
                return;
            }

            // No workflow (active or paused) on prospects anywhere: nothing can be
            // waiting (cached, no query) - the case of every event when workflows
            // are not used
            if (!app(WorkflowTriggerResolver::class)->isWatching(WorkflowSubject::PROSPECT)) {
                return;
            }

            WakeWorkflowExecutions::dispatch($event, $prospectId)->afterCommit();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Prospects created in bulk (sheet synchronisation)
     *
     * @param  int[]  $ids
     */
    public static function prospectsCreated(int $projectId, array $ids): void
    {
        try {
            if (WorkflowTriggerResolver::suppressed() || !$ids) {
                return;
            }

            if (!app(WorkflowTriggerResolver::class)->hasSubject(WorkflowSubject::PROSPECT, $projectId)) {
                return;
            }

            EvaluateWorkflowTriggers::dispatch(WorkflowSubject::PROSPECT, $ids, $projectId, 'created', time());
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * An import is over: report its prospects
     */
    public static function importFinished(int $importId): void
    {
        try {
            if (app(WorkflowTriggerResolver::class)->hasSubject(WorkflowSubject::PROSPECT)) {
                DispatchImportedProspectTriggers::dispatch($importId);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected static function projectId(string $subjectType, Model $model): ?int
    {
        switch ($subjectType) {
            case WorkflowSubject::PROSPECT:
                return $model->project_id;

            case WorkflowSubject::ORDER:
                return Prospect::withoutGlobalScopes()->whereKey($model->prospect_id)->value('project_id');

            case WorkflowSubject::EVENT:
                return Calendar::withoutGlobalScopes()->whereKey($model->calendar_id)->value('project_id');
        }

        return null;
    }
}
