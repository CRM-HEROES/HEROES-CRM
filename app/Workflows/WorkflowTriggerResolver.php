<?php

namespace App\Workflows;

use App\Models\Workflow;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Finds the workflows an event concerns and starts them.
 *
 * Event driven: the code that detects the event (observer, listener, import)
 * calls fire(); there is no scan of the prospects.
 *
 * The active workflows of all the projects are kept in a small cached index
 * (a few entries per workflow, no graph), rebuilt with two queries whenever
 * a workflow or a version changes. Looking up the workflows of an event, and
 * above all deciding that an event concerns none (the usual case, for every
 * prospect created or updated), costs no query at all.
 *
 *  [ 'subjects' => ['prospect' => true, ...],
 *    'projects' => [ projectId => [
 *        'subjects' => ['prospect' => true],
 *        'triggers' => [ 'prospect.created' => [ entry, ... ] ],
 *    ] ] ]
 *
 *  entry: workflow_id, version_id, trigger, baselined, config (trigger node
 *  configuration: conditions, exit_on_leave, minutes_before)
 */
class WorkflowTriggerResolver
{
    public const INDEX_KEY = 'workflows:index';

    /**
     * Depth of the "triggers are disabled" sections (see withoutTriggers)
     */
    protected static int $suppressed = 0;

    public function __construct(protected WorkflowExecutionManager $executions)
    {
    }

    /**
     * Run $callback without recording CRM events as workflow triggers.
     *
     * Used around the actions of a workflow: what a workflow changes on a
     * prospect must not start workflows (itself included) again, otherwise
     * "when a prospect is updated, update it" would never end.
     */
    public static function withoutTriggers(callable $callback)
    {
        self::$suppressed++;

        try {
            return $callback();
        } finally {
            self::$suppressed--;
        }
    }

    public static function suppressed(): bool
    {
        return self::$suppressed > 0;
    }

    public static function forgetIndex(): void
    {
        Cache::forget(self::INDEX_KEY);
    }

    public function index(): array
    {
        return Cache::rememberForever(self::INDEX_KEY, fn () => $this->buildIndex());
    }

    /**
     * Does any active workflow work on this subject type (in this project)?
     */
    public function hasSubject(string $subjectType, ?int $projectId = null): bool
    {
        $index = $this->index();

        return $projectId === null
            ? isset($index['subjects'][$subjectType])
            : isset($index['projects'][$projectId]['subjects'][$subjectType]);
    }

    /**
     * Is there a workflow, active OR paused, on this subject type?
     *
     * For the events that executions may be waiting for: the executions of a
     * paused workflow are held, and must still be told that their event
     * happened - otherwise they would take the "timeout" way once resumed.
     */
    public function isWatching(string $subjectType): bool
    {
        return isset($this->index()['watching'][$subjectType]);
    }

    /**
     * Active workflows of a project for a trigger
     */
    public function entries(int $projectId, string $trigger): array
    {
        return $this->index()['projects'][$projectId]['triggers'][$trigger] ?? [];
    }

    /**
     * Active workflows of every project for a trigger, by project
     * (for the triggers detected by the scheduler)
     *
     * @return array<int, array> projectId => entries
     */
    public function allEntries(string $trigger): array
    {
        $found = [];

        foreach ($this->index()['projects'] as $projectId => $project) {
            if (isset($project['triggers'][$trigger])) {
                $found[$projectId] = $project['triggers'][$trigger];
            }
        }

        return $found;
    }

    /**
     * @param  string  $trigger  e.g. "prospect.created"
     * @param  string  $key  identifies this occurrence of the event (idempotency),
     *                       e.g. "prospect.created:123"
     * @return int number of executions started
     */
    public function fire(string $trigger, int $projectId, int $subjectId, string $key, array $context = []): int
    {
        $subjectType = WorkflowSubject::fromTrigger($trigger);
        $started = 0;

        foreach ($this->entries($projectId, $trigger) as $entry) {
            if ($this->executions->start(
                $projectId,
                $entry['workflow_id'],
                $entry['version_id'],
                $subjectType,
                $subjectId,
                $key,
                $context
            )) {
                $started++;
            }
        }

        return $started;
    }

    protected function buildIndex(): array
    {
        $index = ['subjects' => [], 'watching' => [], 'projects' => []];

        $rows = DB::table('workflows')
            ->join('workflow_versions', 'workflow_versions.id', '=', 'workflows.active_version_id')
            ->whereIn('workflows.status', [Workflow::ACTIVE, Workflow::PAUSED])
            ->whereNull('workflows.deleted_at')
            ->get([
                'workflows.id as workflow_id',
                'workflows.project_id',
                'workflows.status',
                'workflow_versions.id as version_id',
                'workflow_versions.trigger_type',
                'workflow_versions.graph',
                'workflow_versions.baselined_at',
            ]);

        foreach ($rows as $row) {
            $subjectType = WorkflowSubject::fromTrigger($row->trigger_type);
            $index['watching'][$subjectType] = true;

            // A paused workflow starts nothing: only active ones have entries
            if ($row->status !== Workflow::ACTIVE) {
                continue;
            }

            $trigger = (new WorkflowGraph(json_decode($row->graph, true) ?: []))->triggerNode();

            $index['subjects'][$subjectType] = true;
            $index['projects'][$row->project_id]['subjects'][$subjectType] = true;
            $index['projects'][$row->project_id]['triggers'][$row->trigger_type][] = [
                'workflow_id' => $row->workflow_id,
                'version_id' => $row->version_id,
                'trigger' => $row->trigger_type,
                'baselined' => $row->baselined_at !== null,
                'config' => Arr::except($trigger['config'] ?? [], ['trigger']),
            ];
        }

        return $index;
    }
}
