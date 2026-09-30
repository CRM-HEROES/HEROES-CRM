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
use App\Models\Order;
use App\Models\Prospect;
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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Paginate campaigns
        $skip = 0;
        $take = 100;

        do {
            $campaigns = $this->campaigns($skip, $take);
            CampaignModel::whereIn('id', $campaigns->pluck('id'))->update(['pending' => true]);

            foreach($campaigns as $campaign) {
                $this->handleCampaign($campaign);
            }

            $skip += $take;
        } while ($campaigns->count() >= $take);
    }
    
    /**
     * Campaigns
     */
    protected function campaigns($skip, $take)
    {
        $this->date = $this->option('date') ? $this->option('date') . " 00:00" : \Carbon\Carbon::now()->format("Y-m-d h:i");

        return CampaignModel
            ::when(
                $this->option('id'), 
                function($query) {
                    $query->whereIn('id', explode(',', $this->option('id')));
                }, 
                function($query) {
                    $query
                        ->when($this->option('frequency'), function($query) {
                            $query->when(
                                $this->option('frequency') == 'once', 
                                // Frequency once
                                function($query) {
                                    $query->where(function($query) {
                                        $query
                                            ->where('frequency', 'once')
                                            ->whereBetween('date', [$this->date . ':00', $this->date . ':59']);
                                    });
                                // Other frequencies
                                }, function($query) {
                                    $query->where(function($query) {
                                        $query
                                            ->where('frequency', '!=', 'once')
                                            ->where('date', '>=', $this->date . ':00');
                                    });
                                }
                            );
                        })
                        ->where('active', true)
                        ->where('pending', false)
                        ->where('processing', false);
                }
            )
            ->orderBy('project_id')
            ->orderBy('order')
            ->skip($skip)
            ->take($take)
            ->get();
    }
    
    /**
     * Campaign matching
     */
    protected function matching(CampaignModel $campaign, CampaignAction $action, $skip, $take) {
        return $campaign
            ->matching()
            ->when(!$campaign->re_process_processed_prospects, function($query) use($campaign, $action) {
                $campaign->notProcessed($query, $action);
            })
            ->skip($skip)
            ->take($take)
            ->get();
    }
    
    /**
     * Handle campaign
     */
    public function handleCampaign(CampaignModel $campaign) {
        // Check if campaign
        // has valid rules
        // before processing it
        try {
            $campaign->matching();
        } catch (\Exception $e) {
            // Wrong rule
            $campaign->update(['error' => true, 'pending' => false]);
            return;
        }

        // Campaign processing
        $campaign->update([
            'error' => false,
            'begin_at' => \Carbon\Carbon::now(), 
            'pending' => false,
            'processing' => true,
            'execution' => $campaign->execution + 1
        ]);

        // Process all campaign actions
        $actions = $campaign
            ->actions()
            ->withPivot(['begin_at', 'end_at', 'processing'])
            ->get();

        foreach ($actions as $action) {
            $this->handleCampaignAction($campaign, $action);
        }

        // Campaign processed
        // (certain actions may be queued and not finished
        // even if the campaign is processed)
        $campaign->update([
            'end_at' => \Carbon\Carbon::now(),
            'processing' => false
        ]);
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
            'begin_at' => \Carbon\Carbon::now(),
            'processing' => true
        ]);

        $skip = 0;
        $take = 100;

        // Paginate matching models
        do {
            $models = $this->matching($campaign, $action, $skip, $take);

            foreach($models as $model) {
                $this->handleCampaignActionOnModel($campaign, $actionHandler, $model);
            }

            $skip += $take;
        } while ($models->count() >= $take);

        // Action processed
        $action->pivot->update([
            'end_at' => \Carbon\Carbon::now(),
            'processing' => false
        ]);
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
        } else {
            return;
        }

        // Action on model processing
        DB::table($table)
            ->updateOrInsert([
                    $item_id => $model->id,
                    'campaign_id' => $campaign->id,
                    'action_id' => $actionHandler->getAction()->id,
            ], [
                'begin_at' => \Carbon\Carbon::now(),
                'error' => false,
                'processing' => true,
                'execution' => $campaign->execution
            ]);
            
        // Associate the model to the action handler
        $actionHandler->setModel($model);

        try {
            $actionHandler->handle();
        } catch (\Exception $e) {
            DB::table($table)
                ->where($item_id, $model->id)
                ->where('campaign_id', $campaign->id)
                ->where('action_id', $actionHandler->getAction()->id)
                ->limit(1)
                ->update([
                    'error' => true
                ]);
        }
        
        // Action on model processed
        DB::table($table)
            ->where($item_id, $model->id)
            ->where('campaign_id', $campaign->id)
            ->where('action_id', $actionHandler->getAction()->id)
            ->limit(1)
            ->update([
                'end_at' => \Carbon\Carbon::now(),
                'processing' => false
            ]);
    }
}
