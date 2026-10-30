<?php

namespace App\Jobs\Workflow;

use App\Models\Campaign;
use App\Models\Workflow;
use App\Workflows\FilterTransitionDetector;
use App\Workflows\WorkflowExecutionManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Gives to a converted campaign's workflow the prospects that already match
 * and that the campaign had not processed yet.
 *
 * A filter trigger only reacts to prospects that ENTER the filter, so the ones
 * that were already inside when the workflow took over would never start it.
 * They are selected with the query of the campaign itself (its rules, minus the
 * prospects already recorded as processed by its first action): exactly the
 * prospects its next pass would have processed.
 *
 * Idempotent: the execution of a prospect has a unique key shared with the
 * filter trigger ("once per prospect"), so running this twice, or a prospect
 * that enters the filter later, never starts it twice. Incremental: pages of
 * ids by primary key, a bounded number of pages per job, then a new job.
 */
class BackfillLegacyCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public const PAGE = 500;
    public const PAGES_PER_JOB = 20;

    public $tries = 3;

    public function __construct(public int $workflowId, public int $campaignId, public int $afterId = 0)
    {
        $this->onQueue(config('workflows.queue'));
    }

    public function handle(WorkflowExecutionManager $executions): void
    {
        $workflow = Workflow::query()->find($this->workflowId);
        $campaign = Campaign::query()->find($this->campaignId);

        // Only for an active workflow: a paused or archived one must not start anything
        if (!$workflow || !$campaign || $workflow->status !== Workflow::ACTIVE || !$workflow->active_version_id) {
            return;
        }

        $first = $campaign->actions()->orderBy('campaign_action.id')->first();

        if (!$first) {
            return;
        }

        $last = $this->afterId;

        for ($page = 0; $page < self::PAGES_PER_JOB; $page++) {
            $query = $campaign->matching();
            $table = $query->getModel()->getTable();

            if (!$campaign->re_process_processed_prospects) {
                $campaign->notProcessed($query, $first);
            }

            $ids = $query->where("$table.id", '>', $last)->reorder("$table.id")->limit(self::PAGE)->pluck("$table.id");

            foreach ($ids as $id) {
                $executions->start(
                    $workflow->project_id,
                    $workflow->id,
                    $workflow->active_version_id,
                    'prospect',
                    (int) $id,
                    FilterTransitionDetector::onceKey('prospect.filter_entered', (int) $id)
                );
            }

            $last = $ids->isNotEmpty() ? (int) $ids->last() : $last;

            if ($ids->count() < self::PAGE) {
                return;
            }
        }

        self::dispatch($this->workflowId, $this->campaignId, $last);
    }
}
