<?php

namespace App\Workflows;

use App\Models\WorkflowExecution;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Differential detection for the "enters a filter" / "leaves a filter" triggers.
 *
 * For each workflow, the table workflow_filter_memberships holds the subjects
 * that currently match its filter (no row = does not match). After a subject
 * changed, the detector evaluates the filter once for all the changed subjects
 * (ONE query for a batch of ids, in SQL) and compares with the stored state:
 *
 *   matches now, no row   -> it ENTERED the filter  (row added)
 *   no match now, a row   -> it LEFT the filter     (row removed)
 *   same state            -> nothing, no write
 *
 * Whoever adds / removes the row owns the transition, so two workers handling
 * the same change, or a replayed job, fire the trigger once.
 *
 * On leaving, a workflow started by "enters a filter" may stop (cancel its
 * pending / waiting executions of that subject) when its trigger is
 * configured with "exit_on_leave": true. Otherwise it goes on until its end.
 *
 * A version is only evaluated once its memberships were initialised
 * (BaselineFilterMemberships), otherwise every subject already matching would
 * look like a new arrival.
 */
class FilterTransitionDetector
{
    public const TABLE = 'workflow_filter_memberships';

    public function __construct(
        protected WorkflowTriggerResolver $resolver,
        protected WorkflowExecutionManager $executions,
        protected ConditionEvaluator $conditions,
    ) {
    }

    /**
     * @param  int[]  $ids  subjects that were created or updated
     */
    public function detect(int $projectId, string $subjectType, array $ids): void
    {
        if (!$ids) {
            return;
        }

        $entries = array_merge(
            $this->resolver->entries($projectId, "$subjectType.filter_entered"),
            $this->resolver->entries($projectId, "$subjectType.filter_left"),
        );

        foreach ($entries as $entry) {
            if (!$entry['baselined']) {
                continue;
            }

            $matching = $this->matching($projectId, $subjectType, $ids, $entry['config']);

            $known = DB::table(self::TABLE)
                ->where('workflow_id', $entry['workflow_id'])
                ->where('subject_type', $subjectType)
                ->whereIn('subject_id', $ids)
                ->pluck('subject_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach (array_diff($matching, $known) as $id) {
                $this->entered($entry, $projectId, $subjectType, $id);
            }

            foreach (array_diff($known, $matching) as $id) {
                $this->left($entry, $projectId, $subjectType, $id);
            }
        }
    }

    /**
     * Ids, among $ids, that satisfy the filter
     *
     * @return int[]
     */
    protected function matching(int $projectId, string $subjectType, array $ids, array $config): array
    {
        $query = WorkflowSubject::query($subjectType, $projectId);
        $column = $query->getModel()->getTable() . '.id';

        $query->whereIn($column, $ids);
        $this->conditions->constrain($query, $subjectType, $projectId, $config);

        return $query->pluck($column)->map(fn ($id) => (int) $id)->all();
    }

    protected function entered(array $entry, int $projectId, string $subjectType, int $id): void
    {
        $owned = DB::table(self::TABLE)->insertOrIgnore([
            'workflow_id' => $entry['workflow_id'],
            'subject_type' => $subjectType,
            'subject_id' => $id,
            'created_at' => now(),
        ]);

        if (!$owned || $entry['trigger'] !== "$subjectType.filter_entered") {
            return;
        }

        try {
            $this->start($entry, $projectId, $subjectType, $id);
        } catch (\Throwable $e) {
            // Give the transition back so that a retry can fire it
            $this->forget($entry, $subjectType, $id);
            throw $e;
        }
    }

    protected function left(array $entry, int $projectId, string $subjectType, int $id): void
    {
        if (!$this->forget($entry, $subjectType, $id)) {
            return;
        }

        if ($entry['trigger'] === "$subjectType.filter_left") {
            try {
                $this->start($entry, $projectId, $subjectType, $id);
            } catch (\Throwable $e) {
                DB::table(self::TABLE)->insertOrIgnore([
                    'workflow_id' => $entry['workflow_id'],
                    'subject_type' => $subjectType,
                    'subject_id' => $id,
                    'created_at' => now(),
                ]);
                throw $e;
            }

            return;
        }

        if (!empty($entry['config']['exit_on_leave'])) {
            WorkflowExecution::query()
                ->where('workflow_id', $entry['workflow_id'])
                ->where('subject_type', $subjectType)
                ->where('subject_id', $id)
                ->whereIn('status', [WorkflowExecution::PENDING, WorkflowExecution::WAITING])
                ->update([
                    'status' => WorkflowExecution::CANCELLED,
                    'error' => 'The subject left the filter.',
                    'finished_at' => now(),
                    'resume_at' => null,
                    'waiting_event' => null,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Each transition is a new occurrence (a subject can enter, leave and
     * enter again): the idempotency is the ownership of the transition above.
     *
     * With "once_per_subject" on the trigger, the occurrence is the subject
     * itself: it starts the workflow the first time it enters the filter and
     * never again (the unique key of the execution refuses the others). This
     * is how a campaign behaves: a prospect is processed once.
     */
    protected function start(array $entry, int $projectId, string $subjectType, int $id): void
    {
        $this->executions->start(
            $projectId,
            $entry['workflow_id'],
            $entry['version_id'],
            $subjectType,
            $id,
            empty($entry['config']['once_per_subject'])
                ? $entry['trigger'] . ':' . $id . ':' . Str::uuid()
                : self::onceKey($entry['trigger'], $id)
        );
    }

    /**
     * Idempotency key of an execution that must exist once per subject
     * (also used by the backfill of a converted campaign)
     */
    public static function onceKey(string $trigger, int $subjectId): string
    {
        return "$trigger:$subjectId:once";
    }

    protected function forget(array $entry, string $subjectType, int $id): int
    {
        return DB::table(self::TABLE)
            ->where('workflow_id', $entry['workflow_id'])
            ->where('subject_type', $subjectType)
            ->where('subject_id', $id)
            ->delete();
    }
}
