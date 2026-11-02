<?php

namespace App\Console\Commands;

use App\Workflows\WorkflowEvents;
use App\Workflows\WorkflowSubject;
use App\Workflows\WorkflowTriggerResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Safety net of the "enters a filter" triggers: labels attached by paths that
 * do not report to the workflows (webhooks, imports, scripts...) are found
 * here, a minute later at most.
 *
 * One indexed range query on the labels attached lately, and nothing at all
 * for the projects without an active workflow on prospects. Looking twice at
 * a prospect costs nothing: a filter only fires when a prospect goes in.
 */
class WorkflowScanLabels extends Command
{
    protected $signature = 'workflows:scan-labels {--minutes=3}';

    protected $description = 'Report to the workflows the labels attached recently, whatever the way they were attached';

    public function handle(WorkflowTriggerResolver $resolver)
    {
        if (!$resolver->hasSubject(WorkflowSubject::PROSPECT)) {
            return;
        }

        $since = now()->subMinutes(max(1, (int) $this->option('minutes')));

        $rows = DB::table('prospect_label')
            ->join('prospects', 'prospects.id', '=', 'prospect_label.prospect_id')
            ->where('prospect_label.created_at', '>=', $since)
            ->limit(5000)
            ->get(['prospects.project_id', 'prospects.id']);

        foreach ($rows->groupBy('project_id') as $projectId => $group) {
            WorkflowEvents::membershipsChanged((int) $projectId, $group->pluck('id')->all());
        }
    }
}
