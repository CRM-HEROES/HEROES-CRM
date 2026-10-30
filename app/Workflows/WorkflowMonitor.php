<?php

namespace App\Workflows;

use App\Models\Prospect;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Models\WorkflowNodeExecution;
use App\Models\WorkflowVersion;
use Illuminate\Support\Collection;

/**
 * What the follow-up screens show, read from the execution tables.
 *
 * Built to stay cheap whatever the volume: lists are paginated without a
 * total (no COUNT over millions of rows), everything a page needs is loaded
 * with a fixed number of queries (workflows, subjects, versions and steps are
 * fetched for the whole page at once), and the figures that cannot be
 * computed portably in SQL are computed on a bounded, recent sample.
 */
class WorkflowMonitor
{
    /** Recent rows read to compute averages and provider usage */
    public const SAMPLE = 2000;

    /** Executions of one subject shown on its page */
    public const HISTORY = 30;

    /**
     * A page of executions (without the steps: see detail())
     */
    public function executions(int $projectId, array $filters, int $perPage): array
    {
        $page = WorkflowExecution::query()
            ->where('project_id', $projectId)
            ->when(!empty($filters['workflow']), fn ($q) => $q->where('workflow_id', $filters['workflow']))
            ->when(!empty($filters['status']), fn ($q) => $q->whereIn('status', explode(',', $filters['status'])))
            ->when(!empty($filters['subject_type']), fn ($q) => $q->where('subject_type', $filters['subject_type']))
            ->when(!empty($filters['subject_id']), fn ($q) => $q->where('subject_id', $filters['subject_id']))
            ->orderByDesc('id')
            ->simplePaginate($perPage, [
                'id', 'workflow_id', 'workflow_version_id', 'subject_type', 'subject_id', 'status',
                'current_node_id', 'resume_at', 'waiting_event', 'error', 'started_at', 'finished_at',
            ]);

        $items = collect($page->items());
        $workflows = $this->workflowNames($items->pluck('workflow_id'));
        $subjects = $this->prospectNames($items);

        return [
            'data' => $items->map(fn (WorkflowExecution $e) => $this->summary($e, $workflows, $subjects))->all(),
            'current_page' => $page->currentPage(),
            'has_more' => $page->hasMorePages(),
        ];
    }

    /**
     * One execution with its steps
     */
    public function detail(WorkflowExecution $execution): array
    {
        return $this->withSteps(collect([$execution]))[0];
    }

    /**
     * What happened to a prospect: the latest executions of the project, with their steps
     */
    public function prospectHistory(int $projectId, int $prospectId): array
    {
        $executions = WorkflowExecution::query()
            ->where('project_id', $projectId)
            ->where('subject_type', WorkflowSubject::PROSPECT)
            ->where('subject_id', $prospectId)
            ->orderByDesc('id')
            ->limit(self::HISTORY)
            ->get();

        return $this->withSteps($executions);
    }

    /**
     * Executions with their steps: the workflows, versions and steps of all of
     * them are read at once (three queries, however many executions).
     *
     * @return array<int, array>
     */
    protected function withSteps(Collection $executions): array
    {
        if ($executions->isEmpty()) {
            return [];
        }

        $workflows = $this->workflowNames($executions->pluck('workflow_id'));
        $subjects = $this->prospectNames($executions);

        // What each step is called in the graph the execution runs on
        $graphs = WorkflowVersion::query()
            ->whereIn('id', $executions->pluck('workflow_version_id')->unique())
            ->get(['id', 'graph'])
            ->mapWithKeys(fn ($version) => [$version->id => collect($version->graph['nodes'] ?? [])->keyBy('id')]);

        $rows = WorkflowNodeExecution::query()
            ->whereIn('execution_id', $executions->pluck('id'))
            ->orderBy('id')
            ->get()
            ->groupBy('execution_id');

        return $executions->map(function (WorkflowExecution $execution) use ($workflows, $subjects, $graphs, $rows) {
            $nodes = $graphs[$execution->workflow_version_id] ?? collect();
            $current = $execution->current_node_id ? $nodes->get($execution->current_node_id) : null;

            return $this->summary($execution, $workflows, $subjects) + [
                'waiting_for' => $execution->status === WorkflowExecution::WAITING && $current
                    ? ['node_id' => $current['id'], 'type' => $current['type'], 'name' => $current['name'] ?? null]
                    : null,
                'steps' => ($rows[$execution->id] ?? collect())->map(function (WorkflowNodeExecution $row) use ($nodes) {
                    $node = $nodes->get($row->node_id, []);

                    return [
                        'node_id' => $row->node_id,
                        'type' => $row->node_type,
                        'name' => $node['name'] ?? null,
                        'action' => $node['config']['action'] ?? null,
                        'trigger' => $node['config']['trigger'] ?? null,
                        'status' => $row->status,
                        'branch' => $row->branch,
                        'attempts' => $row->attempts,
                        'result' => $row->result,
                        'error' => $row->error,
                        'started_at' => $row->started_at,
                        'finished_at' => $row->finished_at,
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    }

    /**
     * Figures of the project: what is going on now, and what happened over the
     * last days.
     */
    public function stats(int $projectId, int $days): array
    {
        $since = now()->subDays($days);
        $active = [WorkflowExecution::PENDING, WorkflowExecution::RUNNING, WorkflowExecution::WAITING];

        // Now (whatever their age)
        $current = WorkflowExecution::query()
            ->where('project_id', $projectId)
            ->whereIn('status', $active)
            ->selectRaw('workflow_id, status, count(*) as total')
            ->groupBy('workflow_id', 'status')
            ->get();

        // Over the period
        $period = WorkflowExecution::query()
            ->where('project_id', $projectId)
            ->whereIn('status', WorkflowExecution::FINISHED)
            ->where('finished_at', '>=', $since)
            ->selectRaw('workflow_id, status, count(*) as total')
            ->groupBy('workflow_id', 'status')
            ->get();

        $dates = WorkflowExecution::query()
            ->where('project_id', $projectId)
            ->selectRaw('workflow_id, max(started_at) as last_started_at, min(case when status = ? then resume_at end) as next_resume_at', [WorkflowExecution::WAITING])
            ->groupBy('workflow_id')
            ->get()
            ->keyBy('workflow_id');

        // Average duration, on the latest completed executions
        $durations = WorkflowExecution::query()
            ->where('project_id', $projectId)
            ->where('status', WorkflowExecution::COMPLETED)
            ->where('finished_at', '>=', $since)
            ->whereNotNull('started_at')
            ->orderByDesc('id')
            ->limit(self::SAMPLE)
            ->get(['workflow_id', 'started_at', 'finished_at'])
            ->groupBy('workflow_id')
            ->map(fn ($group) => (int) round($group->avg(fn ($e) => max(0, $e->started_at->diffInSeconds($e->finished_at)))));

        $workflows = Workflow::query()
            ->where('project_id', $projectId)
            ->orderBy('name')
            ->get(['id', 'name', 'status'])
            ->map(function (Workflow $workflow) use ($current, $period, $dates, $durations) {
                $count = fn (Collection $rows, string $status) => (int) ($rows->where('workflow_id', $workflow->id)->where('status', $status)->first()->total ?? 0);

                return [
                    'id' => $workflow->id,
                    'name' => $workflow->name,
                    'status' => $workflow->status,
                    'current' => [
                        'waiting' => $count($current, WorkflowExecution::WAITING),
                        'running' => $count($current, WorkflowExecution::RUNNING),
                        'pending' => $count($current, WorkflowExecution::PENDING),
                    ],
                    'period' => [
                        'completed' => $count($period, WorkflowExecution::COMPLETED),
                        'failed' => $count($period, WorkflowExecution::FAILED),
                        'cancelled' => $count($period, WorkflowExecution::CANCELLED),
                    ],
                    'last_started_at' => $dates[$workflow->id]->last_started_at ?? null,
                    'next_resume_at' => $dates[$workflow->id]->next_resume_at ?? null,
                    'average_seconds' => $durations[$workflow->id] ?? null,
                ];
            })
            ->values();

        // Steps, by kind and outcome, over the period
        $steps = WorkflowNodeExecution::query()
            ->join('workflow_executions', 'workflow_executions.id', '=', 'workflow_node_executions.execution_id')
            ->where('workflow_executions.project_id', $projectId)
            ->where('workflow_node_executions.created_at', '>=', $since)
            ->selectRaw('workflow_node_executions.node_type as type, workflow_node_executions.status as status, count(*) as total')
            ->groupBy('workflow_node_executions.node_type', 'workflow_node_executions.status')
            ->get()
            ->groupBy('type')
            ->map(fn ($rows) => $rows->pluck('total', 'status'));

        // The most frequent reasons of failure
        $errors = WorkflowExecution::query()
            ->where('project_id', $projectId)
            ->where('status', WorkflowExecution::FAILED)
            ->where('finished_at', '>=', $since)
            ->whereNotNull('error')
            ->selectRaw('error as message, count(*) as total')
            ->groupBy('error')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => ['message' => $row->message, 'count' => (int) $row->total])
            ->all();

        return [
            'days' => $days,
            'workflows' => $workflows->all(),
            'steps' => $steps,
            'errors' => $errors,
            'providers' => $this->providerUsage($projectId, $since),
        ];
    }

    /**
     * Communications by operator over the period, from the latest action steps
     * (the operator is recorded in the result of the step, success or not).
     */
    protected function providerUsage(int $projectId, $since): array
    {
        $usage = [];

        WorkflowNodeExecution::query()
            ->join('workflow_executions', 'workflow_executions.id', '=', 'workflow_node_executions.execution_id')
            ->where('workflow_executions.project_id', $projectId)
            ->where('workflow_node_executions.node_type', WorkflowGraph::ACTION)
            ->where('workflow_node_executions.created_at', '>=', $since)
            ->orderByDesc('workflow_node_executions.id')
            ->limit(self::SAMPLE)
            ->get(['workflow_node_executions.status', 'workflow_node_executions.result'])
            ->each(function ($row) use (&$usage) {
                $provider = $row->result['provider'] ?? null;

                if ($provider) {
                    $usage[$provider]['provider'] = $provider;
                    $usage[$provider]['sent'] = ($usage[$provider]['sent'] ?? 0) + ($row->status === WorkflowNodeExecution::COMPLETED ? 1 : 0);
                    $usage[$provider]['failed'] = ($usage[$provider]['failed'] ?? 0) + ($row->status === WorkflowNodeExecution::FAILED ? 1 : 0);
                }
            });

        return array_values($usage);
    }

    protected function summary(WorkflowExecution $execution, Collection $workflows, Collection $subjects): array
    {
        return [
            'id' => $execution->id,
            'workflow' => ['id' => $execution->workflow_id, 'name' => $workflows[$execution->workflow_id] ?? null],
            'subject' => [
                'type' => $execution->subject_type,
                'id' => $execution->subject_id,
                'name' => $execution->subject_type === WorkflowSubject::PROSPECT ? ($subjects[$execution->subject_id] ?? null) : null,
            ],
            'status' => $execution->status,
            'current_node_id' => $execution->current_node_id,
            'waiting_event' => $execution->waiting_event,
            'resume_at' => $execution->resume_at,
            'error' => $execution->error,
            'started_at' => $execution->started_at,
            'finished_at' => $execution->finished_at,
        ];
    }

    /**
     * Names of the workflows (deleted ones included: their history stays readable)
     */
    protected function workflowNames(Collection $ids): Collection
    {
        return Workflow::withTrashed()->whereIn('id', $ids->unique())->pluck('name', 'id');
    }

    /**
     * Names of the prospects of a set of executions, with the usual access
     * rules: a prospect the user may not see has no name here.
     */
    protected function prospectNames(Collection $executions): Collection
    {
        $ids = $executions->where('subject_type', WorkflowSubject::PROSPECT)->pluck('subject_id')->unique();

        if ($ids->isEmpty()) {
            return collect();
        }

        return Prospect::query()
            ->whereIn('id', $ids)
            ->get(['id', 'first_name', 'last_name', 'company_name'])
            ->mapWithKeys(fn (Prospect $prospect) => [
                $prospect->id => trim("{$prospect->first_name} {$prospect->last_name}") ?: $prospect->company_name,
            ]);
    }
}
