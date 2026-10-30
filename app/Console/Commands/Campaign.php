<?php

namespace App\Console\Commands;

use App\Console\Commands\Campaign\Action;

use App\Console\Commands\Campaign\Event\ProspectAttachGroupAction as EventProspectAttachGroupAction;
use App\Console\Commands\Campaign\Event\ProspectAttachLabelAction as EventProspectAttachLabelAction;
use App\Console\Commands\Campaign\Event\ProspectAttachUserAction as EventProspectAttachUserAction;
use App\Console\Commands\Campaign\Event\ProspectDetachGroupAction as EventProspectDetachGroupAction;
use App\Console\Commands\Campaign\Event\ProspectDetachLabelAction as EventProspectDetachLabelAction;
use App\Console\Commands\Campaign\Event\ProspectDetachUserAction as EventProspectDetachUserAction;
use App\Console\Commands\Campaign\Event\ProspectEventAction as EventProspectEventAction;
use App\Console\Commands\Campaign\Event\ProspectMessageAction as EventProspectMessageAction;
use App\Console\Commands\Campaign\Event\ProspectNotProcessedAction as EventProspectNotProcessedAction;
use App\Console\Commands\Campaign\Event\ProspectProcessedAction as EventProspectProcessedAction;
use App\Console\Commands\Campaign\Event\ProspectSmsAction as EventProspectSmsAction;

use App\Console\Commands\Campaign\Prospect\ProspectAttachGroupAction;
use App\Console\Commands\Campaign\Prospect\ProspectAttachLabelAction;
use App\Console\Commands\Campaign\Prospect\ProspectAttachUserAction;
use App\Console\Commands\Campaign\Prospect\ProspectDeleteAction;
use App\Console\Commands\Campaign\Prospect\ProspectDetachGroupAction;
use App\Console\Commands\Campaign\Prospect\ProspectDetachLabelAction;
use App\Console\Commands\Campaign\Prospect\ProspectDetachUserAction;
use App\Console\Commands\Campaign\Prospect\ProspectEventAction;
use App\Console\Commands\Campaign\Prospect\ProspectFileDocumentAction;
use App\Console\Commands\Campaign\Prospect\ProspectGetLatLngAction;
use App\Console\Commands\Campaign\Prospect\ProspectMessageAction;
use App\Console\Commands\Campaign\Prospect\ProspectMessageDocumentAction;
use App\Console\Commands\Campaign\Prospect\ProspectNotProcessedAction;
use App\Console\Commands\Campaign\Prospect\ProspectOrderAction;
use App\Console\Commands\Campaign\Prospect\ProspectProcessedAction;
use App\Console\Commands\Campaign\Prospect\ProspectSmsAction;

use App\Console\Commands\Campaign\Order\OrderAttachLabelAction;
use App\Console\Commands\Campaign\Order\ProspectAttachGroupAction as OrderProspectAttachGroupAction;
use App\Console\Commands\Campaign\Order\ProspectAttachLabelAction as OrderProspectAttachLabelAction;
use App\Console\Commands\Campaign\Order\ProspectAttachUserAction as OrderProspectAttachUserAction;
use App\Console\Commands\Campaign\Order\ProspectDetachGroupAction as OrderProspectDetachGroupAction;
use App\Console\Commands\Campaign\Order\ProspectDetachLabelAction as OrderProspectDetachLabelAction;
use App\Console\Commands\Campaign\Order\ProspectDetachUserAction as OrderProspectDetachUserAction;
use App\Console\Commands\Campaign\Order\ProspectEventAction as OrderProspectEventAction;
use App\Console\Commands\Campaign\Order\ProspectMessageAction as OrderProspectMessageAction;
use App\Console\Commands\Campaign\Order\ProspectNotProcessedAction as OrderProspectNotProcessedAction;
use App\Console\Commands\Campaign\Order\ProspectProcessedAction as OrderProspectProcessedAction;
use App\Console\Commands\Campaign\Order\ProspectSmsAction as OrderProspectSmsAction;

use App\Console\Commands\Campaign\Order\OrderAttachProductAction;
use App\Console\Commands\Campaign\Order\OrderAttachStepAction;
use App\Console\Commands\Campaign\Order\OrderDeleteAction;
use App\Console\Commands\Campaign\Order\OrderDetachLabelAction;
use App\Console\Commands\Campaign\Order\OrderDetachProductAction;
use App\Console\Commands\Campaign\Order\OrderDetachStepAction;
use App\Console\Commands\Campaign\Order\OrderGenerateInvoiceAction;
use App\Console\Commands\Campaign\Order\OrderSignInvoiceAction;
use App\Console\Commands\Campaign\Order\OrderUserActionAction;
use App\Console\Commands\Campaign\Order\ProspectFileInvoiceAction;
use App\Console\Commands\Campaign\Order\ProspectMessageInvoiceAction;

use App\Models\Campaign as CampaignModel;
use App\Models\CampaignAction;
use App\Models\Event;
use App\Models\Order;
use App\Models\Prospect;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Campaign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:campaign {--frequency=} {--id=} {--date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prospects Campaign';

    /**
     * 
     */
    protected $date;

    /**
     * 
     */
    protected $actionHandlers;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->actionHandlers = [
            'prospect' => [
                'prospect-attach-group'     => new ProspectAttachGroupAction(),
                'prospect-attach-label'     => new ProspectAttachLabelAction(),
                'prospect-attach-user'      => new ProspectAttachUserAction(),
                'prospect-delete'           => new ProspectDeleteAction(),
                'prospect-detach-group'     => new ProspectDetachGroupAction(),
                'prospect-detach-label'     => new ProspectDetachLabelAction(),
                'prospect-detach-user'      => new ProspectDetachUserAction(),
                'prospect-event'            => new ProspectEventAction(),
                'prospect-get-lat-lng'      => new ProspectGetLatLngAction(),
                'prospect-file-document'    => new ProspectFileDocumentAction(),
                'prospect-message'          => new ProspectMessageAction(),
                'prospect-message-document' => new ProspectMessageDocumentAction(),
                'prospect-not-processed'    => new ProspectNotProcessedAction(),
                'prospect-order'            => new ProspectOrderAction(),
                'prospect-processed'        => new ProspectProcessedAction(),
                'prospect-sms'              => new ProspectSmsAction(),
            ],
            'order' => [
                'order-attach-label'     => new OrderAttachLabelAction(),
                'order-attach-product'   => new OrderAttachProductAction(),
                'order-attach-step'      => new OrderAttachStepAction(),
                'order-detach-label'     => new OrderDetachLabelAction(),
                'order-detach-product'   => new OrderDetachProductAction(),
                'order-detach-step'      => new OrderDetachStepAction(),
                'order-delete'           => new OrderDeleteAction(),
                'order-generate-invoice' => new OrderGenerateInvoiceAction(),
                'order-sign-invoice'     => new OrderSignInvoiceAction(),
                'order-user-action'      => new OrderUserActionAction(),

                'prospect-attach-group'    => new OrderProspectAttachGroupAction(),
                'prospect-attach-label'    => new OrderProspectAttachLabelAction(),
                'prospect-attach-user'     => new OrderProspectAttachUserAction(),
                'prospect-detach-group'    => new OrderProspectDetachGroupAction(),
                'prospect-detach-label'    => new OrderProspectDetachLabelAction(),
                'prospect-detach-user'     => new OrderProspectDetachUserAction(),
                'prospect-event'           => new OrderProspectEventAction(),
                'prospect-file-invoice'    => new ProspectFileInvoiceAction(),
                'prospect-message'         => new OrderProspectMessageAction(),
                'prospect-message-invoice' => new ProspectMessageInvoiceAction(),
                'prospect-not-processed'   => new OrderProspectNotProcessedAction(),
                'prospect-processed'       => new OrderProspectProcessedAction(),
                'prospect-sms'             => new OrderProspectSmsAction(),
            ],
            'event' => [
                'prospect-attach-group'    => new EventProspectAttachGroupAction(),
                'prospect-attach-label'    => new EventProspectAttachLabelAction(),
                'prospect-attach-user'     => new EventProspectAttachUserAction(),
                'prospect-detach-group'    => new EventProspectDetachGroupAction(),
                'prospect-detach-label'    => new EventProspectDetachLabelAction(),
                'prospect-detach-user'     => new EventProspectDetachUserAction(),
                'prospect-event'           => new EventProspectEventAction(),
                'prospect-message'         => new EventProspectMessageAction(),
                'prospect-not-processed'   => new EventProspectNotProcessedAction(),
                'prospect-processed'       => new EventProspectProcessedAction(),
                'prospect-sms'             => new EventProspectSmsAction(),
            ]
            // ...
        ];
    }


    /**
     * Action handlers, by campaign target then action
     * (reused by the workflow engine)
     */
    public function getActionHandlers(): array
    {
        return $this->actionHandlers;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Only the ids are loaded up front (ordered by project / order):
        // the campaigns themselves are loaded page by page. Paginating the
        // live query with skip/take is not safe because processing a
        // campaign changes the very flags the query filters on.
        foreach ($this->campaignIds()->chunk(100) as $ids) {
            $campaigns = CampaignModel
                ::whereIn('id', $ids)
                ->orderBy('project_id')
                ->orderBy('order')
                ->orderBy('id')
                ->get();

            foreach ($campaigns as $campaign) {
                $this->handleCampaign($campaign);
            }
        }
    }

    /**
     * Current date time, in the campaign timezone.
     * The campaign date is a naive local date time: it must be compared
     * with the local current time, not with the server (UTC) one.
     */
    protected function now(): Carbon
    {
        $timezone = config('campaign.timezone');

        return $this->option('date')
            ? Carbon::parse($this->option('date'), $timezone)
            : Carbon::now($timezone);
    }

    /**
     * Ids of the campaigns to run now, in execution order
     */
    protected function campaignIds()
    {
        $this->date = $this->now();

        // Manual run of specific campaigns
        if ($this->option('id')) {
            return CampaignModel
                ::whereIn('id', explode(',', $this->option('id')))
                ->orderBy('project_id')
                ->orderBy('order')
                ->orderBy('id')
                ->pluck('id');
        }

        $query = CampaignModel
            ::where('active', true)
            ->where('pending', false)
            ->where('processing', false);

        $frequency = $this->option('frequency');

        if (!$frequency) {
            return $query
                ->orderBy('project_id')
                ->orderBy('order')
                ->orderBy('id')
                ->pluck('id');
        }

        $query
            ->where('frequency', $frequency)
            // The campaign date is its start: not before it
            ->where('date', '<=', $this->date->format('Y-m-d H:i:s'));

        // "Once": only the campaigns that have never been executed.
        // (The date is a lower bound, not an exact minute: a missed
        // scheduler tick no longer makes the campaign skip its run.)
        if ($frequency == 'once') {
            $query->where('execution', 0);
        }

        $campaigns = $query
            ->orderBy('project_id')
            ->orderBy('order')
            ->orderBy('id')
            ->get(['id', 'date']);

        // Daily / monthly campaigns run at the hour (and day of the month)
        // of their date. They are small sets: filtered here, which stays
        // portable (no DB specific HOUR() / DAY() functions).
        if ($frequency == 'daily') {
            $campaigns = $campaigns->filter(function($campaign) {
                return Carbon::parse($campaign->date)->hour == $this->date->hour;
            });
        } else if ($frequency == 'monthly') {
            $campaigns = $campaigns->filter(function($campaign) {
                $date = Carbon::parse($campaign->date);

                // The 31st runs on the last day of shorter months
                return min($date->day, $this->date->daysInMonth) == $this->date->day &&
                    $date->hour == $this->date->hour;
            });
        }

        return $campaigns->pluck('id');
    }

    /**
     * Campaign matching (one page, keyset pagination).
     *
     * Paging by id and not by offset: the matching set shrinks while it is
     * processed (items processed by the action are excluded from it, and
     * actions such as archive / label / delete change the filters), so an
     * offset would skip items.
     */
    protected function matching(CampaignModel $campaign, CampaignAction $action, $lastId, $take) {
        $query = $campaign->matching();
        $table = $query->getModel()->getTable();

        return $query
            ->when(!$campaign->re_process_processed_prospects, function($query) use($campaign, $action) {
                $campaign->notProcessed($query, $action);
            })
            ->where("$table.id", '>', $lastId)
            ->reorder("$table.id")
            ->limit($take)
            ->get();
    }

    /**
     * Handle campaign
     */
    public function handleCampaign(CampaignModel $campaign) {
        // Claim the campaign atomically: two overlapping runs
        // must never process the same campaign
        $claimed = CampaignModel
            ::where('id', $campaign->id)
            ->where('pending', false)
            ->where('processing', false)
            ->update(['pending' => true]);

        if (!$claimed) {
            return;
        }

        $campaign->forceFill(['pending' => true])->syncOriginalAttribute('pending');

        // Check if campaign
        // has valid rules
        // before processing it
        try {
            $campaign->matching();
        } catch (\Throwable $e) {
            // Wrong rule
            $campaign->update(['error' => true, 'pending' => false]);
            return;
        }

        // Campaign processing
        $campaign->update([
            'error' => false,
            'begin_at' => Carbon::now(),
            'pending' => false,
            'processing' => true,
            'execution' => $campaign->execution + 1
        ]);

        try {
            // Process all campaign actions
            $actions = $campaign
                ->actions()
                ->withPivot(['begin_at', 'end_at', 'processing'])
                ->get();

            foreach ($actions as $action) {
                $this->handleCampaignAction($campaign, $action);
            }
        } catch (\Throwable $e) {
            report($e);
            $campaign->update(['error' => true]);
        } finally {
            // Campaign processed
            // (certain actions may be queued and not finished
            // even if the campaign is processed)
            // Always released, otherwise a crash would lock the
            // campaign (processing = true) forever.
            $campaign->update([
                'end_at' => Carbon::now(),
                'processing' => false,
                'pending' => false,
            ]);
        }
    }

    /**
     * Handle campaign action
     */
    public function handleCampaignAction(CampaignModel $campaign, CampaignAction $action) {
        // Check if handler for the action exists
        if (
            !isset($this->actionHandlers[$campaign->for]) ||
            !isset($this->actionHandlers[$campaign->for][$action->action])
        ) {
            $campaign
                ->actions()
                ->updateExistingPivot($action->id, [
                    'processing' => false,
                    'error' => true
                ]);
            return;
        }

        $actionHandler = $this->actionHandlers[$campaign->for][$action->action];
        // Associate the action to the handler
        $actionHandler->setAction($action);

        // Action processing
        $action->pivot->update([
            'begin_at' => Carbon::now(),
            'processing' => true,
            'error' => false
        ]);

        $take = 100;
        $lastId = 0;

        try {
            // Paginate matching models
            do {
                $models = $this->matching($campaign, $action, $lastId, $take);

                foreach($models as $model) {
                    $this->handleCampaignActionOnModel($campaign, $actionHandler, $model);
                }

                if ($models->isNotEmpty()) {
                    $lastId = $models->last()->id;
                }
            } while ($models->count() >= $take);
        } catch (\Throwable $e) {
            report($e);
            $action->pivot->update(['error' => true]);
        } finally {
            // Action processed
            $action->pivot->update([
                'end_at' => Carbon::now(),
                'processing' => false
            ]);
        }
    }

    /**
     * Handle campaign action on model
     */
    public function handleCampaignActionOnModel(CampaignModel $campaign, Action $actionHandler, $model) {
        if ($model instanceof Prospect) {
            $table = "campaign_prospect";
            $item_id = "prospect_id";
        } else if ($model instanceof Order) {
            $table = "campaign_order";
            $item_id = "order_id";
        } else if ($model instanceof Event) {
            $table = "campaign_event";
            $item_id = "event_id";
        } else {
            return;
        }

        $actionId = $actionHandler->getAction()->id;

        // Action on model processing
        DB::table($table)
            ->updateOrInsert([
                    $item_id => $model->id,
                    'campaign_id' => $campaign->id,
                    'action_id' => $actionId,
            ], [
                'begin_at' => Carbon::now(),
                'error' => false,
                'processing' => true,
                'execution' => $campaign->execution
            ]);

        // Associate the model to the action handler
        $actionHandler->setModel($model);

        try {
            $actionHandler->handle();
        } catch (\Throwable $e) {
            report($e);

            DB::table($table)
                ->where($item_id, $model->id)
                ->where('campaign_id', $campaign->id)
                ->where('action_id', $actionId)
                ->limit(1)
                ->update([
                    'error' => true
                ]);
        }

        // Action on model processed
        DB::table($table)
            ->where($item_id, $model->id)
            ->where('campaign_id', $campaign->id)
            ->where('action_id', $actionId)
            ->limit(1)
            ->update([
                'end_at' => Carbon::now(),
                'processing' => false
            ]);
    }
}
