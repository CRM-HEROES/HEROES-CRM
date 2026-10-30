<?php

namespace App\Jobs\Workflow;

use App\Models\Workflow;
use App\Models\WorkflowVersion;
use App\Workflows\ConditionEvaluator;
use App\Workflows\FilterTransitionDetector;
use App\Workflows\WorkflowGraph;
use App\Workflows\WorkflowSubject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

/**
 * Initialises the memberships of a filter trigger: records the subjects that
 * ALREADY match the filter, without firing anything, so that only the later
 * changes are seen as "enters" / "leaves".
 *
 * Incremental: pages of 1000 matching ids by primary key, a bounded number of
 * pages per job, then the job continues in a new one. Never loads the
 * subjects in memory. The version is marked as initialised at the end.
 */
class BaselineFilterMemberships implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public const PAGE = 1000;
    public const PAGES_PER_JOB = 20;

    public $tries = 3;

    public function __construct(public int $workflowId, public int $versionId, public int $afterId = 0)
    {
        $this->onQueue(config('workflows.queue'));
    }

    public function handle(ConditionEvaluator $conditions): void
    {
        $workflow = Workflow::query()->select('id', 'project_id', 'active_version_id')->find($this->workflowId);
        $version = WorkflowVersion::query()->find($this->versionId);

        // The workflow moved on to another version: this one is obsolete
        if (!$workflow || !$version || $workflow->active_version_id !== $version->id) {
            return;
        }

        $trigger = (new WorkflowGraph($version->graph))->triggerNode();
        $config = $trigger['config'] ?? [];
        $subjectType = WorkflowSubject::fromTrigger($config['trigger']);

        $last = $this->afterId;

        for ($page = 0; $page < self::PAGES_PER_JOB; $page++) {
            $query = WorkflowSubject::query($subjectType, $workflow->project_id);
            $column = $query->getModel()->getTable() . '.id';

            $query->where($column, '>', $last)->orderBy($column)->limit(self::PAGE);
            $conditions->constrain($query, $subjectType, $workflow->project_id, $config);

            $ids = $query->pluck($column)->all();

            if ($ids) {
                DB::table(FilterTransitionDetector::TABLE)->insertOrIgnore(array_map(fn ($id) => [
                    'workflow_id' => $workflow->id,
                    'subject_type' => $subjectType,
                    'subject_id' => $id,
                    'created_at' => now(),
                ], $ids));

                $last = (int) end($ids);
            }

            if (count($ids) < self::PAGE) {
                $version->update(['baselined_at' => now()]);
                return;
            }
        }

        // More to do: continue in a new job
        self::dispatch($this->workflowId, $this->versionId, $last);
    }
}
