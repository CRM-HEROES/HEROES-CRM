<?php

namespace App\Models;

use App\Filters\EventFilters;
use App\Filters\Filters;
use App\Filters\OrderFilters;
use App\Filters\ProspectFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        'name',
        'description',
        'active',
        'pending',
        'processing',
        'error',
        'for',
        'frequency',
        'date',
        'begin_at',
        'end_at',
        'execution',
        'style',
        're_process_processed_prospects'
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'style' => 'json',
    ];


    // Relationships

    /**
     * Actions
     */
    public function actions()
    {
        return $this->belongsToMany(CampaignAction::class, 'campaign_action', 'campaign_id', 'action_id');
    }

    /**
     * 
     */
    public function matching()
    {
        // Matching prospects
        if ($this->for == "prospect") {
            // Only not processed prospects
            // in the current project
            $query = $this->project->prospects()/*->whereNull('processed_at')*/;
            return $this->matchingItems($query, new ProspectFilters());
            
        // Matching orders
        } else if ($this->for == "order") {
            // Only orders that belongs to 
            // not processed prospects
            // in the current project
            $query = Order::whereHas('prospect', function($query) {
                $query
                ->where('project_id', $this->project_id)
                ->whereNull('processed_at');
            });

            return $this->matchingItems($query, new OrderFilters());

        // Matching appointments
        } else if ($this->for == "event") {
            // Only appointments of the project calendars
            // that are linked to a prospect (all the event
            // actions are applied to that prospect).
            // The calendar global scope is bypassed: the campaign
            // runs on behalf of the project, not of a user.
            $query = Event::query()
                ->whereIn('events.calendar_id', Calendar::withoutGlobalScopes()
                    ->where('project_id', $this->project_id)
                    ->select('calendars.id'))
                ->whereNotNull('events.prospect_id');

            return $this->matchingItems($query, new EventFilters());
        }

        throw new \InvalidArgumentException("Unsupported campaign target: " . $this->for);
    }

    /**
     * Prospects/Orders
     * that have not yet been processed 
     * by the current campaign
     */
    public function notProcessed($query, $action)
    {
        // Prospect
        if ($this->for == "prospect") {
            return $query->whereDoesntHave('campaigns', function($query) use($action) {
                $query
                    ->where('campaigns.id', $this->id)
                    ->where('campaign_prospect.action_id', $action->id);
            });
        // Order
        } else if ($this->for == "order") {
            return $query->whereDoesntHave('campaigns', function($query) use($action) {
                $query
                    ->where('campaigns.id', $this->id)
                    ->where('campaign_order.action_id', $action->id);
            });
        // Event
        } else if ($this->for == "event") {
            return $query->whereDoesntHave('campaigns', function($query) use($action) {
                $query
                    ->where('campaigns.id', $this->id)
                    ->where('campaign_event.action_id', $action->id);
            });
        }
    }

    /**
     * Matching prospects/orders
     */
    public function matchingItems($query, Filters $filter)
    {
        $rules = $this->rules()->withPivot('error')->get();

        $query = $query
            ->where(function($query) use($filter, $rules) {
                $closure = "orWhere";

                foreach ($rules->filter(function($rule) {
                    return $rule->type == 'filter';
                }) as $rule) {
                    $this->closureRuleQuery($closure, $filter, $query, $rule);
                }
        
                foreach ($this->operators as $operator) {
                    $this->closureOperatorQuery($closure, $filter, $query, $operator);
                }
            });
            
        // Count filters cannot be applied
        // in and / or closure
        // ($query is a relation for the prospects: the filters are typed on the
        // Eloquent builder it wraps, and would fail with a TypeError on the relation)
        $builder = $query instanceof \Illuminate\Database\Eloquent\Relations\Relation
            ? $query->getQuery()
            : $query;

        foreach ($rules->filter(function($rule) {
            return $rule->type == 'count';
        }) as $rule) {
            foreach ($rule->rule as $k => $v) {
                $filter->{$k}($builder, $v);
            }
        }

        return $query;
    }

    /**
     * Operators
     */
    public function operators()
    {
        return $this->belongsToMany(CampaignOperator::class, 'campaign_operator', 'campaign_id', 'operator_id');
    }

    /**
     * Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Prospects
     */
    public function prospects()
    {
        return $this->belongsToMany(Prospect::class, 'campaign_prospect', 'campaign_id', 'prospect_id');
    }

    /**
     * Rules
     */
    public function rules()
    {
        return $this->belongsToMany(CampaignRule::class, 'campaign_rule', 'campaign_id', 'rule_id');
    }
    

    // Others

    /**
     */
    protected function operatorQuery(CampaignOperator $operator, Filters $filters, $query)
    {
        $closure = $operator->and ? "where" : "orWhere";

        foreach ($operator->rules()->withPivot('error')->get() as $rule) {
            $this->closureRuleQuery($closure, $filters, $query, $rule);
        }

        foreach ($operator->operators as $op) {
            $this->closureOperatorQuery($closure, $filters, $query, $op);
        }
    }

    /**
     */
    protected function closureRuleQuery($closure, Filters $filters, $query, CampaignRule $rule)
    {
        try {
            if ($rule->rule) {
                $query->{$closure}(function($q) use($filters, $rule) {
                    foreach ($rule->rule as $k => $v) {
                        $filters->{$k}($q, $v);
                    }
                });
            }

            if ($rule->pivot->error) {
                $rule->pivot->update(['error' => false]);
            }
        } catch (\Throwable $e) {
            // Throwable: an unknown filter name raises an \Error
            // (undefined method), not an \Exception
            if (!$rule->pivot->error) {
                $rule->pivot->update(['error' => true]);
            }
            throw $e;
        }
    }

    /**
     */
    protected function closureOperatorQuery($closure, Filters $filters, $query, CampaignOperator $operator)
    {
        $query->{$closure}(function($q) use($filters, $operator) {
            $this->operatorQuery($operator, $filters, $q);
        });
    }
}
