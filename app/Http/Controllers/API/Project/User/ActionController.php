<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\File;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ActionController extends Controller
{

    /**
     * Retrieve actions
     * made by user
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    public function index(Project $project, User $user)
    {
        $page = request()->input('page', 1) - 1;
        $count = request()->input('count', 30);

    	$actions = 
    			    $this->createProspect            ($project, $user)
    		// ->union($this->log                     ($project, $user))
    		->union($this->updateProspect            ($project, $user))
    		->union($this->showProspect              ($project, $user))
    		->union($this->createProspectInteraction ($project, $user))
    		->union($this->createProspectSms         ($project, $user))
    		->union($this->affectProspectLabel       ($project, $user))
    		->union($this->affectProspectGroup       ($project, $user))
    		->union($this->affectProspectUser        ($project, $user))
            ->union($this->createImport              ($project, $user))
            ->union($this->makeExport                ($project, $user))
    		->union($this->affectEvent               ($project, $user))
    		->union($this->createEvent               ($project, $user))
    		->union($this->updateEvent               ($project, $user))
    		->union($this->createOrder               ($project, $user))
    		->union($this->addOrderProduct           ($project, $user))
    		->union($this->addOrderAction            ($project, $user))
    		->union($this->addOrderStep              ($project, $user))
    		->union($this->createInvoice             ($project, $user))
    		->union($this->createCampaign            ($project, $user))
            ->union($this->createDocuments           ($project, $user))
            ->union($this->createProspectFiles       ($project, $user))
            ->union($this->createProspectMessages    ($project, $user))
    		->orderBy('date', 'desc')
            ->offset($page * $count)
    		->limit($count)
    		->get();

		$actions->transform(function($action) use($project) {
			$action->content = $this->getActionContent($project, $action);
            $action->action_at = $action->date;
			$action->date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $action->date)->diffForHumans();

			return $action;
		});

        return $actions;
    }

    /**
     * Retrieve prospects
     * that has been created
     * by the user
     * and are associated to the project
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function createProspect(Project $project, User $user)
    {
    	return DB::table('prospects')
    		->select(
    			DB::raw("prospects.created_at AS date, 'create-prospect' AS type, prospects.id AS id")
    		)
    		->where('project_id', '=', $project->id)
            ->where('project_id', $project->id)
    		->where('creator_id', $user->id);
    }

    /**
     * Retrieve prospects revisions
     * by the user
     * and are associated to the project
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function log(Project $project, User $user)
    {
    	return DB::table('tracker_logs')
    		->select(
    			DB::raw("tracker_logs.created_at AS date, 'log' AS type, tracker_logs.id AS id")
    		)
    		->join('tracker_route_paths', 'tracker_route_paths.id', '=', 'tracker_logs.route_path_id')
    		->where('tracker_route_paths.path', 'LIKE', 'api/project/' . $project->slug . '/%')
    		->where('tracker_logs.user_id', $user->id)
    		->where('tracker_logs.is_ajax', 0);
    }

    /**
     * Retrieve prospects updates
     * by the user
     * and are associated to the project
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function updateProspect(Project $project, User $user)
    {
    	return DB::table('revisions')
    		->select(
    			DB::raw("revisions.created_at AS date, 'update-prospect' AS type, revisions.id AS id")
    		)
    		->join('prospects', 'prospects.id', '=', 'revisions.revisionable_id')
    		->where('prospects.project_id', '=', $project->id)
    		->where('revisions.user_id', $user->id)
    		->where('revisions.revisionable_type', 'App\Models\Prospect')
    		->where('revisions.key', '!=', 'meta');
    }

    /**
     * Retrieve prospect info
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function showProspect(Project $project, User $user)
    {
    	return DB::table('tracker_logs')
    		->select(
    			DB::raw("tracker_logs.created_at AS date, 'show-prospect' AS type, tracker_route_paths.path AS id")
    		)
    		->join('tracker_route_paths', 'tracker_route_paths.id', '=', 'tracker_logs.route_path_id')
    		->join('tracker_routes', 'tracker_routes.id', '=', 'tracker_route_paths.route_id')
    		->where('tracker_route_paths.path', 'LIKE', 'api/project/' . $project->slug . '/prospect/%')
    		->where('tracker_routes.name', 'api.project.prospect.show')
    		->where('tracker_logs.user_id', $user->id);
    }

    /**
     * Retrieve interaction
     * with prospect
     * made by the user
     * and are associated to the project
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function createProspectInteraction(Project $project, User $user)
    {
    	return DB::table('interactions')
    		->select(
    			DB::raw("interactions.created_at AS date, 'prospect-interaction' AS type, interactions.id AS id")
    		)
    		->join('prospects', 'prospects.id', '=', 'interactions.prospect_id')
    		->where('prospects.project_id', '=', $project->id)
    		->where('interactions.creator_id', $user->id);
    }

    /**
     * Retrieve sms sent to the prospect
     * by the user
     * and are associated to the project
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function createProspectSms(Project $project, User $user)
    {
    	return DB::table('sms')
    		->select(
    			DB::raw("sms.created_at AS date, 'prospect-sms' AS type, sms.id AS id")
    		)
    		->join('prospects', 'prospects.id', '=', 'sms.prospect_id')
    		->where('prospects.project_id', '=', $project->id)
    		->where('sms.creator_id', $user->id);
    }

    /**
     * Retrieve label assignments to prospects
     * made by the user
     * and are associated to the project
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function affectProspectLabel(Project $project, User $user)
    {
    	return DB::table('prospect_label')
    		->select(
    			DB::raw("prospect_label.created_at AS date, 'prospect-label' AS type, CONCAT(prospect_label.prospect_id, ',', prospect_label.label_id) AS id")
    		)
    		->join('prospects', 'prospect_label.prospect_id', '=', 'prospects.id')
    		->where('prospects.project_id', '=', $project->id)
    		->where('prospect_label.creator_id', $user->id);
    }

    /**
     * Retrieve group assignments to prospects
     * made by the user
     * and are associated to the project
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function affectProspectGroup(Project $project, User $user)
    {
    	return DB::table('prospect_group')
    		->select(
    			DB::raw("prospect_group.created_at AS date, 'prospect-group' AS type, CONCAT(prospect_group.prospect_id, ',', prospect_group.group_id) AS id")
    		)
    		->join('prospects', 'prospect_group.prospect_id', '=', 'prospects.id')
    		->where('prospects.project_id', '=', $project->id)
    		->where('prospect_group.creator_id', $user->id);
    }

    /**
     * Retrieve user assignments to prospects
     * made by the user
     * and are associated to the project
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function affectProspectUser(Project $project, User $user)
    {
    	return DB::table('prospect_user')
    		->select(
    			DB::raw("prospect_user.created_at AS date, 'prospect-user' AS type, CONCAT(prospect_user.prospect_id, ',', prospect_user.user_id) AS id")
    		)
    		->join('prospects', 'prospect_user.prospect_id', '=', 'prospects.id')
    		->where('prospects.project_id', '=', $project->id)
    		->where('prospect_user.creator_id', $user->id);
    }

    /**
     * Retrieve imports
     * created by the user
     * and are associated to the project
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function createImport(Project $project, User $user)
    {
        return DB::table('imports')
            ->select(DB::raw("imports.created_at AS date, 'create-import' AS type, imports.id AS id"))
            ->where('imports.project_id', '=', $project->id)
            ->where('imports.creator_id', $user->id);
    }

    /**
     * Retrieve exports
     * made by the user
     * and are associated to the project
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function makeExport(Project $project, User $user)
    {
        return DB::table('exports')
            ->select(DB::raw("exports.created_at AS date, 'make-export' AS type, exports.id AS id"))
            ->where('exports.project_id', '=', $project->id)
            ->where('exports.creator_id', $user->id);
    }

    /**
     * Retrieve appointment assignments to other employees
     * made by the user
     * and are associated to the project
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function affectEvent(Project $project, User $user)
    {
    	return DB::table('events')
    		->select(DB::raw("events.created_at AS date, 'affect-event' AS type, events.id AS id"))
    		->join('calendars', 'calendars.id', '=', 'events.calendar_id')
    		->where('events.creator_id', $user->id)
    		->where('calendars.project_id', $project->id)
    		->whereRaw("events.user_id <> events.creator_id");
    }

    /**
     * Retrieve appointments
     * created by the user
     * and are associated to the project
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function createEvent(Project $project, User $user)
    {
    	return DB::table('events')
    		->select(DB::raw("events.created_at AS date, 'create-event' AS type, events.id AS id"))
    		->join('calendars', 'calendars.id', '=', 'events.calendar_id')
    		->where('calendars.project_id', $project->id)
    		->where('events.user_id', $user->id);
    }

    /**
     * Retrieve events updates
     * by the user
     * and are associated to the project
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function updateEvent(Project $project, User $user)
    {
    	return DB::table('revisions')
    		->select(
    			DB::raw("revisions.created_at AS date, 'update-event' AS type, revisions.id AS id")
    		)
    		->join('events', 'events.id', '=', 'revisions.revisionable_id')
    		->join('calendars', 'calendars.id', '=', 'events.calendar_id')
    		->where('calendars.project_id', '=', $project->id)
    		->where('revisions.user_id', $user->id)
    		->where('revisions.revisionable_type', 'App\Models\Event');
    }

    /**
     * Retrieve order
     * created by the user
     * and are associated to the project
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function createOrder(Project $project, User $user)
    {
    	return DB::table('orders')
    		->select(DB::raw("orders.created_at AS date, 'create-order' AS type, orders.id AS id"))
    		->join('prospects', 'prospects.id', '=', 'orders.prospect_id')
    		->where('prospects.project_id', $project->id)
    		->where('orders.creator_id', $user->id);
    }

    /**
     * Retrieve order products
     * associated by the user
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function addOrderProduct(Project $project, User $user)
    {
    	return DB::table('order_product')
    		->select(DB::raw("order_product.created_at AS date, 'add-order-product' AS type, CONCAT(order_product.order_id, ',', order_product.product_id) AS id"))
    		->join('products', 'products.id', '=', 'order_product.product_id')
    		->where('products.project_id', $project->id)
    		->where('order_product.creator_id', $user->id);
    }

    /**
     * Retrieve order actions
     * associated by the user
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function addOrderAction(Project $project, User $user)
    {
    	return DB::table('order_action')
    		->select(DB::raw("order_action.created_at AS date, 'add-order-action' AS type, CONCAT(order_action.order_id, ',', order_action.action_id) AS id"))
    		->join('order_actions', 'order_actions.id', '=', 'order_action.action_id')
    		->where('order_actions.project_id', $project->id)
    		->where('order_action.creator_id', $user->id);
    }

    /**
     * Retrieve order steps
     * associated by the user
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function addOrderStep(Project $project, User $user)
    {
    	return DB::table('order_step')
    		->select(DB::raw("order_step.created_at AS date, 'add-order-step' AS type, CONCAT(order_step.order_id, ',', order_step.step_id) AS id"))
    		->join('order_steps', 'order_steps.id', '=', 'order_step.step_id')
    		->where('order_steps.project_id', $project->id)
    		->where('order_step.creator_id', $user->id);
    }

    /**
     * Retrieve order invoices
     * created by the user
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function createInvoice(Project $project, User $user)
    {
    	return DB::table('invoices')
    		->select(DB::raw("invoices.created_at AS date, 'create-invoice' AS type, invoices.id AS id"))
    		->join('documents', 'documents.id', '=', 'invoices.document_id')
    		->where('documents.project_id', $project->id)
    		->where('invoices.creator_id', $user->id);
    }

    /**
     * Retrieve prospects campaigns
     * created by the user
     * and are associated to the project
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function createCampaign(Project $project, User $user)
    {
    	return DB::table('campaigns')
    		->select(
    			DB::raw("campaigns.created_at AS date, 'create-campaign' AS type, campaigns.id AS id")
    		)
    		->where('campaigns.creator_id', $user->id)
    		->where('campaigns.project_id', $project->id);
    }

    /**
     * Retrieve document templates
     * created by the user
     * and are associated to the project
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function createDocuments(Project $project, User $user)
    {
        return DB::table('documents')
            ->select(
                DB::raw("documents.created_at AS date, 'create-document' AS type, documents.id AS id")
            )
            ->where('documents.creator_id', $user->id)
            ->where('documents.project_id', $project->id);
    }

    /**
     * Retrieve files added to a folder
     * by the user
     * and are associated to the project
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function createProspectFiles(Project $project, User $user)
    {
        return DB::table('files')
            ->select(
                DB::raw("files.created_at AS date, 'create-prospect-file' AS type, files.id AS id")
            )
            ->join('prospects', 'prospects.id', '=', 'files.prospect_id')
            ->where('prospects.project_id', $project->id)
            ->where('files.creator_id', $user->id);
    }

    /**
     * Retrieve message added to a thread
     * by the user
     * and are associated to the project
     * 
     * @param  \App\Project  $project
     * @param  \App\User  $user
     */
    protected function createProspectMessages(Project $project, User $user)
    {
        return DB::table('messages')
            ->select(
                DB::raw("messages.created_at AS date, 'add-prospect-message' AS type, messages.id AS id")
            )
            ->join('threads', 'threads.id', '=', 'messages.thread_id')
            ->where('messages.creator_id', $user->id)
            ->where('threads.project_id', $project->id);
    }

    /**
     * Get action content
     * depending on action type
     */
    protected function getActionContent(Project $project, $action)
    {
    	switch ($action->type) {
    		case 'create-prospect':
    			return $this->getActionContentCreateProspect($project, $action->id);
        
            case 'update-prospect':
                return $this->getActionContentUpdateProspect($project, $action->id);

            case 'show-prospect':
                return $this->getActionContentShowProspect($project, $action->id);
                        
    		case 'log':
    			return $this->getActionContentLog($project, $action->id);
    		
    		case 'prospect-interaction':
    			return $this->getActionContentProspectInteraction($project, $action->id);
    		
    		case 'prospect-sms':
    			return $this->getActionContentProspectSms($project, $action->id);
    		
            case 'prospect-label':
                return $this->getActionContentAffectProspectLabel($project, $action->id);

            case 'prospect-group':
                return $this->getActionContentAffectProspectGroup($project, $action->id);

            case 'prospect-user':
                return $this->getActionContentAffectProspectUser($project, $action->id);
                                    
            case 'create-import':
                return $this->getActionContentCreateImport($project, $action->id);
            
            case 'make-export':
                return $this->getActionContentMakeExport($project, $action->id);
            
    		case 'affect-event':
    			return $this->getActionContentAffectEvent($project, $action->id);
        
            case 'create-event':
                return $this->getActionContentCreateEvent($project, $action->id);

            case 'update-event':
                return $this->getActionContentUpdateEvent($project, $action->id);
        
            case 'create-order':
                return $this->getActionContentCreateOrder($project, $action->id);
                
            case 'add-order-product':
                return $this->getActionContentAddOrderProduct($project, $action->id);
                   
            case 'add-order-action':
                return $this->getActionContentAddOrderAction($project, $action->id);
                   
            case 'add-order-step':
                return $this->getActionContentAddOrderStep($project, $action->id);
                            
            case 'create-invoice':
                return $this->getActionContentCreateInvoice($project, $action->id);
                            
    		case 'create-campaign':
    			return $this->getActionContentCreateCampaign($project, $action->id);
    		
            case 'create-document':
                return $this->getActionContentCreateDocument($project, $action->id);
            
            case 'create-prospect-file':
                return $this->getActionContentCreateProspectFile($project, $action->id);
            
            case 'add-prospect-message':
                return $this->getActionContentAddProspectMessage($project, $action->id);
            
    		default:
    			return "";
    	}
    }

    /**
     * Get action content for prospect creation
     */
    protected function getActionContentCreateProspect(Project $project, $id)
    {
    	// Get prospect from database
    	$prospect = DB::table('prospects')->where('id', $id)->first();

    	if (!$prospect) {
    		return null;
    	}

    	// Get prospect name
    	$prospectName = $prospect->full_name;
        $prospectRoute = route('project.prospect.show', [$project->slug, $prospect->id]);

    	// Content
    	return "a créé le prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a>";
    }

    /**
     * Get action content for page view
     */
    protected function getActionContentLog(Project $project, $id)
    {
    	$log = DB::table('tracker_logs')
    		->join('tracker_route_paths', 'tracker_route_paths.id', '=', 'tracker_logs.route_path_id')
    		->where('tracker_logs.id', $id)
    		->first();

    	if (!$log) {
    		return null;
    	}

		// Get the path of the viewed page
    	$path = url($log->path);

    	// Content
    	return "a visité la page <a target=\"_blank\" href=\"{$path}\">{$path}</a>";
    }

    /**
     * Get action content for prospect update
     */
    protected function getActionContentUpdateProspect(Project $project, $id)
    {
    	$revision = DB::table('revisions')->where('id', $id)->first();

    	if (!$revision) {
    		return null;
    	}


    	// Get prospect
    	$prospect = DB::table('prospects')->where('id', $revision->revisionable_id)->first();

    	if (!$prospect) {
    		return "a modifié un prospect";
    	}

    	$prospectName = $prospect->full_name;
        $prospectRoute = route('project.prospect.show', [$project->slug, $prospect->id]);


    	// Category
    	if (Str::startsWith($revision->key, 'category->')) {
    		$field = $project
                ->categories()
    			->where('id', Str::replace('category->', '', $revision->key))
    			->first();
		// Meta field
    	} else if (Str::startsWith($revision->key, 'meta->')) {
    		$field = $project
                ->fields()
    			->where('slug', Str::replace('meta->', '', $revision->key))
    			->where('meta', true)
    			->first();
		// Default field
    	} else {
    		$field = $project
                ->fields()
    			->where('slug', $revision->key)
    			->where('meta', false)
    			->first();
    	}

    	if (!$field) {
    		return "a modifié le prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a>";
    	}

    	// Content
    	return "a modifié le prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a>, <b>{$field['name']}</b>: '{$revision->new_value}'";
    }

    /**
     * Get action content for prospect show
     */
    protected function getActionContentShowProspect(Project $project, $id)
    {
        $prospectId = substr($id, strrpos($id, '/') + 1);
        
    	// Get prospect
    	$prospect = DB::table('prospects')->where('id', $prospectId)->first();

        if (!$prospect) {
            return null;
        }
        
    	$prospectName = $prospect->full_name;
        $prospectRoute = route('project.prospect.show', [$project->slug, $prospect->id]);

    	return "a consulté les informations du prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a>";
    }

    /**
     * Get action content for prospect interaction
     */
    protected function getActionContentProspectInteraction(Project $project, $id)
    {
    	$interaction = DB::table('interactions')
    		->select(DB::raw('prospects.full_name as prospect_full_name, prospects.id as prospect_id, interactions.from_user as from_user, interactions.source as source'))
    		->join('prospects', 'prospects.id', '=', 'interactions.prospect_id')
    		->where('interactions.id', $id)
    		->first();

    	if (!$interaction) {
    		return null;
    	}

    	$prospectName = implode(' ', array_filter([$interaction->prospect_full_name, $interaction->prospect_full_name]));
        $prospectRoute = route('project.prospect.show', [$project->slug, $interaction->prospect_id]);

        switch ($interaction->source) {
            case 'ringover':
                $source = "Ringover";
                break;
            case 'telephone':
                $source = "Téléphone";
                break;
            case 'aircall':
                $source = "Aircall";
                break;
            case 'whatsapp':
                $source = "Whatsapp";
                break;
            default:
                $source = "(Inconnu)";
                break;
        }

    	// Content
    	if ($interaction->from_user) {
    		return "a appelé le prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a> via {$source}";
    	}

    	return "a reçu l'appel du prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a> via {$source}";
    }

    /**
     * Get action content for prospect sms
     */
    protected function getActionContentProspectSms(Project $project, $id)
    {
    	$sms = DB::table('sms')
    		->select(DB::raw('prospects.id as prospect_id, prospects.full_name as prospect_full_name, sms.message AS message, sms.source AS source'))
    		->join('prospects', 'prospects.id', '=', 'sms.prospect_id')
    		->where('sms.id', $id)
    		->first();

    	if (!$sms) {
    		return null;
    	}

    	$prospectName = implode(' ', array_filter([$sms->prospect_full_name, $sms->prospect_full_name]));
        $prospectRoute = route('project.prospect.show', [$project->slug, $sms->prospect_id]);

        switch ($sms->source) {
            case 'smsbox':
                $source = "SMSBOX";
                break;
            case 'ultramsg':
                $source = "UltraMsg";
                break;
            case 'mtarget':
                $source = "MTarget";
                break;
            case 'ringover':
                $source = "Ringover";
                break;
            case 'telephone':
                $source = "Téléphone";
                break;
            case 'whatsapp':
                $source = "Whatsapp";
                break;
            default:
                $source = "(Inconnu)";
                break;
        }

    	return "a envoyé un sms via {$source} au prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a>:<br>{$sms->message}";
    }

    /**
     * Get action content for prospect label assignment
     */
    protected function getActionContentAffectProspectLabel(Project $project, $id)
    {
    	list($prospectId, $labelId) = explode(',' , $id);

    	// Get prospect
    	$prospect = DB::table('prospects')->where('id', $prospectId)->first();

    	if (!$prospect) {
    		return null;
    	}

    	// Prospect name
    	$prospectName = $prospect->full_name;
        $prospectRoute = route('project.prospect.show', [$project->slug, $prospect->id]);

    	// Get label
    	// with category name
    	$label = DB::table('labels')
    		->select(DB::raw('labels.name as name, labels.color as color, labels.bgcolor as bgcolor, categories.name as category_name'))
    		->join('categories', 'categories.id', '=', 'labels.category_id')
    		->where('labels.id', $labelId)
    		->first();

    	if (!$label) {
    		return null;
    	}

    	// Content
    	return "a affecté le filtre <b>{$label->category_name}</b> <span style=\"background:{$label->bgcolor};color:{$label->color};padding:0 3px;border-radius:3px;font-weight: 400;\">{$label->name}</span> au prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a>";
    }

    /**
     * Get action content for prospect group assignment
     */
    protected function getActionContentAffectProspectGroup(Project $project, $id)
    {
    	list($prospectId, $groupId) = explode(',' , $id);

    	// Get prospect
    	$prospect = DB::table('prospects')->where('id', $prospectId)->first();

    	if (!$prospect) {
    		return null;
    	}

    	// Prospect name
    	$prospectName = $prospect->full_name;
        $prospectRoute = route('project.prospect.show', [$project->slug, $prospect->id]);

    	// Get label
    	// with category name
    	$group = DB::table('groups')
    		->select(DB::raw('groups.name as name, groups.color as color, groups.bgcolor as bgcolor'))
    		->where('groups.id', $groupId)
    		->first();

    	if (!$group) {
    		return null;
    	}

    	// Content
    	return "a affecté le prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a> au groupe <span style=\"background:{$group->bgcolor};color:{$group->color};padding:0 3px;border-radius:3px;font-weight: 400;\">{$group->name}</span>";
    }

    /**
     * Get action content for prospect user assignment
     */
    protected function getActionContentAffectProspectUser(Project $project, $id)
    {
    	list($prospectId, $userId) = explode(',' , $id);

    	// Get prospect
    	$prospect = DB::table('prospects')->where('id', $prospectId)->first();

    	if (!$prospect) {
    		return null;
    	}

    	// Prospect name
    	$prospectName = $prospect->full_name;
        $prospectRoute = route('project.prospect.show', [$project->slug, $prospect->id]);

    	// Get label
    	// with category name
    	$user = DB::table('users')
    		->select(DB::raw('users.name as name'))
    		->where('users.id', $userId)
    		->first();

    	if (!$user) {
    		return null;
    	}

    	// Content
    	return "a affecté le prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a> à l'utilisateur <span style=\"background: #CCC;color: #333;padding:0 3px;border-radius:3px;font-weight: 400;\">{$user->name}</span>";
    }

    /**
     * Get action content for import creation
     */
    protected function getActionContentCreateImport(Project $project, $id)
    {
        $import = DB::table('imports')->where('id', $id)->first();

        if (!$import) {
            return null;
        }

        // Content
        return "a créé l'import <b>{$import->name}</b>";
    }

    /**
     * Get action content for export
     */
    protected function getActionContentMakeExport(Project $project, $id)
    {
        $export = DB::table('exports')->where('id', $id)->first();

        if (!$export) {
            return null;
        }

        $url = route('api.project.export.show', [$project->slug, $id]);

        // Content
        return "a effectué un <a href=\"{$url}\" target=\"_blank\">export</a>";
    }

    /**
     * Get action content for event assignment
     */
    protected function getActionContentAffectEvent(Project $project, $id)
    {
    	// Get event
    	// type
    	// affected user
    	// date
    	$event = DB::table('events')
    		->select(DB::raw('events.id as id, calendars.name as event_type, users.name as user, users.id as user_id, events.started_at as date, events.prospect_id as prospect_id'))
    		->join('calendars', 'calendars.id', '=', 'events.calendar_id')
    		->join('users', 'users.id', '=', 'events.user_id')
    		->where('events.id', $id)
    		->first();

    	if (!$event) {
    		return null;
    	}

        $userRoute = route('project.user.show', [$project->slug, $event->user_id]);

    	// Get event associated prospect
    	// if it exists
    	$prospect = DB::table('prospects')
    		->where('id', $event->prospect_id)
    		->first();

		// If event is associated
		// to a prospect
		if ($prospect) {
	    	$prospectName = $prospect->full_name;
            $prospectRoute = route('project.prospect.show', [$project->slug, $prospect->id]);

	    	return "a affecté un <b>{$event->event_type}</b> avec le prospect <a target=\"_blank\" href=\"$prospectRoute\">{$prospectName}</a> à l'employé <a target=\"_blank\" href=\"{$userRoute}\">{$event->user}</a> pour la date du <b>{$event->date}</b>";
    	}

    	// Content
    	return "a affecté un <b>{$event->event_type}</b> à l'employé <a target=\"_blank\" href=\"{$userRoute}\">{$event->user}</a> pour la date du <b>{$event->date}</b>";
    }

    /**
     * Get action content for event creation
     */
    protected function getActionContentCreateEvent(Project $project, $id)
    {
    	// Get event
    	// type
    	// date
    	$event = DB::table('events')
    		->select(DB::raw('events.id as id, calendars.name as event_type, events.started_at as date, events.prospect_id as prospect_id'))
    		->join('calendars', 'calendars.id', '=', 'events.calendar_id')
    		->where('events.id', $id)
    		->first();

    	if (!$event) {
    		return null;
    	}

    	// Get event associated prospect
    	// if it exists
    	$prospect = DB::table('prospects')
    		->where('id', $event->prospect_id)
    		->first();

		// If event is associated
		// to a prospect
		if ($prospect) {
	    	$prospectName = $prospect->full_name;
            $prospectRoute = route('project.prospect.show', [$project->slug, $prospect->id]);

	    	return "a réservé un <b>{$event->event_type}</b> avec le prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a> pour la date du <b>{$event->date}</b>";
    	}

    	// Content
    	return "a réservé un <b>{$event->event_type}</b> pour la date du <b>{$event->date}</b>";
    }

    /**
     * Get action content for prospect update
     */
    protected function getActionContentUpdateEvent(Project $project, $id)
    {
    	$revision = DB::table('revisions')->where('id', $id)->first();

    	if (!$revision) {
    		return null;
    	}


    	// Get event
    	$event = DB::table('events')->where('id', $revision->revisionable_id)->first();

    	if (!$event) {
    		return "a modifié un RDV";
    	}


    	// Get prospect
    	$prospect = DB::table('prospects')->where('id', $event->prospect_id)->first();

    	if ($prospect) {
            $prospectName = $prospect->full_name;
            $prospectRoute = route('project.prospect.show', [$project->slug, $prospect->id]);
    
            $prospectContent = " du prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a>";
    	} else {
            $prospectContent = "";
        }

    	if ($revision->key == 'started_at') {
    		return "a deplacé un RDV{$prospectContent} à la date du {$event->started_at}";
    	} else if ($revision->key == 'ended_at') {
    		return "a modifié la durée d'un RDV{$prospectContent}";
    	} else if ($revision->key == 'confirmed') {
            if ($revision->new_value) {
                return "a marqué un RDV{$prospectContent} comme confirmé";
            } else {
                return "a marqué un RDV{$prospectContent} comme non confirmé";
            }
    	} else if ($revision->key == 'done') {
            if ($revision->new_value) {
                return "a marqué un RDV{$prospectContent} comme fait";
            } else {
                return "a marqué un RDV{$prospectContent} comme non fait";
            }
    	} else if ($revision->key == 'location') {
    		return "a modifié la location d'un RDV{$prospectContent} en \"{$revision->new_value}\"";
    	} else if ($revision->key == 'name') {
    		return "a modifié le nom d'un RDV{$prospectContent} en \"{$revision->new_value}\"";
    	} else if ($revision->key == 'description') {
    		return "a modifié la description d'un RDV{$prospectContent} en \"{$revision->new_value}\"";
    	} else {
            return null;
    	}
    }

    /**
     * Get action content for order creation
     */
    protected function getActionContentCreateOrder(Project $project, $id)
    {
    	// Get event
    	// type
    	// date
    	$order = Order::find($id);

    	if (!$order) {
    		return null;
    	}

    	// Get event associated prospect
    	// if it exists
    	$prospect = DB::table('prospects')
    		->where('id', $order->prospect_id)
    		->first();

		// If order is associated
		// to a prospect
		if (!$prospect) {
    		return null;
    	}

    	// Content
        $prospectName = $prospect->full_name;
        $prospectRoute = route('project.prospect.show', [$project->slug, $prospect->id]);

        return "a effectué le devis <b>{$order->name}</b> pour le prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a>";
    }

    /**
     * Get action content for adding product to order
     */
    protected function getActionContentAddOrderProduct(Project $project, $id)
    {
    	list($orderId, $productId) = explode(',' , $id);

        $order = Order::find($orderId);

        if (!$order) {
            return null;
        }

    	// Get product
    	$product = $order->products()->where('id', $productId)->first();

    	// Get prospect
    	$prospect = Prospect::find($order->prospect_id);

    	// Prospect name
    	$prospectName = $prospect->full_name;
        $prospectRoute = route('project.prospect.show', [$project->slug, $prospect->id]);

    	// Content
    	return "a ajouté <b>{$product->pivot->quantity} {$product->name} à {$product->pivot->price_value}</b> dans une commande du prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a>";
    }

    /**
     * Get action content for adding action to order
     */
    protected function getActionContentAddOrderAction(Project $project, $id)
    {
    	list($orderId, $actionId) = explode(',' , $id);

        $order = Order::find($orderId);

    	// Get action
    	$action = $order->actions()->where('id', $actionId)->first();

    	// Get user
    	$user = User::find($action->pivot->user_id);

    	// Get prospect
    	$prospect = Prospect::find($order->prospect_id);

    	// Prospect name
    	$prospectName = $prospect->full_name;
        $prospectRoute = route('project.prospect.show', [$project->slug, $prospect->id]);

    	// Content
    	return "a indiqué l'utilisateur <b>{$user->name}</b> comme ayant fait l'action <b>{$action->name}</b> dans une commande du prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a>";
    }

    /**
     * Get action content for adding step to order
     */
    protected function getActionContentAddOrderStep(Project $project, $id)
    {
    	list($orderId, $actionId) = explode(',' , $id);

        $order = Order::find($orderId);

    	// Get step
    	$step = $order->steps()->where('id', $actionId)->first();

    	// Get prospect
    	$prospect = Prospect::find($order->prospect_id);

    	// Prospect name
    	$prospectName = $prospect->full_name;
        $prospectRoute = route('project.prospect.show', [$project->slug, $prospect->id]);

    	// Content
    	return "a ajouté l'étape <b>{$step->name}</b> dans une commande du prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a>";
    }

    /**
     * Get action content for adding step to order
     */
    protected function getActionContentCreateInvoice(Project $project, $id)
    {
        $invoice = Invoice::find($id);

        $order = Order::find($invoice->order_id);

    	// Get step
    	$document = Document::find($invoice->document_id);

    	// Get prospect
    	$prospect = Prospect::find($order->prospect_id);

    	// Prospect name
    	$prospectName = $prospect->full_name;
        $prospectRoute = route('project.prospect.show', [$project->slug, $prospect->id]);

    	// Content
    	return "a généré la facture <b>{$document->name}</b> pour une commande du prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a>";
    }

    /**
     * Get action content for campaign creation
     */
    protected function getActionContentCreateCampaign(Project $project, $id)
    {
    	$campaign = DB::table('campaigns')->where('id', $id)->first();

    	if (!$campaign) {
    		return null;
    	}

    	// Content
    	return "a créé la campagne '{$campaign->name}'";
    }

    /**
     * Get action content for document template creation
     */
    protected function getActionContentCreateDocument(Project $project, $id)
    {
        $document = DB::table('documents')->where('id', $id)->first();

        if (!$document) {
            return null;
        }

        // Content
        return "a créé le modèle de document PDF '{$document->name}'";
    }

    /**
     * Get action content for prospect file adding
     */
    protected function getActionContentCreateProspectFile(Project $project, $id)
    {
        $media = File::find($id);

        if (!$media) {
            return null;
        }

        // Get media associated prospect
        // if it exists
        $prospect = DB::table('prospects')
            ->where('id', $media->prospect_id)
            ->first();

        if (!$prospect) {
            return null;
        }

        $prospectName = $prospect->full_name;
        $prospectRoute = route('project.prospect.show', [$project->slug, $prospect->id]);

        $folder = DB::table('folders')
            ->where('id', $media->folder_id)
            ->first();

        if (!$folder) {
            return "a ajouté le fichier <a target=\"_blank\" href=\"{$media->url}\">{$media->name}</a> pour le prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a>";
        }

        // Content
        return "a ajouté le fichier <a target=\"_blank\" href=\"{$media->url}\">{$media->name}</a> pour le prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a> dans le dossier <b>{$folder->name}</b>";
    }

    /**
     * Get action content for prospect message adding
     */
    protected function getActionContentAddProspectMessage(Project $project, $id)
    {
        $message = DB::table('messages')
            ->select(DB::raw('messages.body as body, prospects.full_name as prospect_full_name, prospects.id as prospect_id, threads.name as thread_name'))
            ->join('prospects', 'prospects.id', '=', 'messages.prospect_id')
            ->join('threads', 'threads.id', '=', 'messages.thread_id')
            ->where('messages.id', $id)
            ->first();

        if (!$message) {
            return null;
        }

        $prospectName = implode(' ', array_filter([$message->prospect_full_name, $message->prospect_full_name]));
        $prospectRoute = route('project.prospect.show', [$project->slug, $message->prospect_id]);

        // Content
        return "a envoyé un message pour le prospect <a target=\"_blank\" href=\"{$prospectRoute}\">{$prospectName}</a> dans le canal de discussion <b>{$message->thread_name}</b>:<br>{$message->body}";
    }

}

