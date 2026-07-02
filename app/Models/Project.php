<?php

namespace App\Models;

use App\Filters\Filters;
use App\Models\Scopes\ProjectScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Project extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new ProjectScope());
    }

    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'capital',
        'city',
        'country', 
        'creator_id',
        'description',
        'email',
        'latitude',
        'logo',
        'longitude',
        'naf',
        'name',
        'num_tva_intra',
        'phone_number',
        'postal_code',
        'siret',
        'slug',
        'street',
        'street_bis',
        'type',
        'job_category',
        'valid_address',
    ];

    /**
     * Get project route by slug
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'logo',
    ];


    /**
     * 
     */
    protected $appends = [
        'thumbnail',
    ];


    // Attributes

    /**
     * Get thumbnail
     *
     * @return string
     */
    public function getThumbnailAttribute()
    {
        return $this->logo ? route('api.project.logo', [
            'project' => $this->slug, 
        ]) : null;
    }

    /**
     * Get project address
     * from street, postal code, city and country info
     *
     * @return string
     */
    public function getAddressAttribute()
    {
        $items = [
            $this->street,
            $this->street_bis,
            $this->postal_code,
            $this->city,
            $this->country,
        ];
        
        // Remove unused space
        $items = array_map(function($item) {
            return trim($item);
        }, $items);

        // Remove empty item
        $items = array_filter($items);

        return implode(' ', $items);
    }


    // Builder

    /**
     * Apply all relevant filters
     *
     * @param Builder $query
     * @param Filters $filters
     * @return Builder
     */
    public function scopeFilter(Builder $builder, Filters $filters)
    {
        return $filters->apply($builder);
    }


    // Relationships

    /**
     * Calendars
     */
    public function activeUsers()
    {
        return $this->hasMany(User::class, 'last_project_id');
    }

    /**
     * Calendars
     */
    public function calendars()
    {
        return $this->hasMany(Calendar::class);
    }

    /**
     * Campaigns
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Campaign Actions
     */
    public function campaignActions()
    {
        return $this->hasMany(CampaignAction::class);
    }

    /**
     * Campaign Operators
     */
    public function campaignOperators()
    {
        return $this->hasMany(CampaignOperator::class);
    }

    /**
     * Campaign Rules
     */
    public function campaignRules()
    {
        return $this->hasMany(CampaignRule::class);
    }

    /**
     * Categories
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Documents
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Event description templates
     */
    public function eventDescriptionTemplates()
    {
        return $this->hasMany(EventDescriptionTemplate::class);
    }

    /**
     * Export
     */
    public function exports()
    {
        return $this->hasMany(Export::class);
    }

    /**
     * Fields
     */
    public function fields()
    {
        return $this->hasMany(Field::class);
    }

    /**
     * Fields
     */
    public function folders()
    {
        return $this->hasMany(Folder::class);
    }

    /**
     * Groups
     */
    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    /**
     * Imports
     */
    public function imports()
    {
        return $this->hasMany(Import::class);
    }

    /**
     * Menus
     */
    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    /**
     * Message templates
     */
    public function messageTemplates()
    {
        return $this->hasMany(MessageTemplate::class);
    }

    /**
     * Metrics
     */
    public function metrics()
    {
        return $this->hasMany(Metric::class);
    }

    /**
     * Ocrs
     */
    public function ocrs()
    {
        return $this->hasMany(Ocr::class);
    }

    /**
     * Orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Order actions
     */
    public function orderActions()
    {
        return $this->hasMany(OrderAction::class);
    }

    /**
     * Order status
     */
    public function orderStatuses()
    {
        return $this->hasMany(OrderStatus::class);
    }

    /**
     * Order steps
     */
    public function orderSteps()
    {
        return $this->hasMany(OrderStep::class);
    }

    /**
     * Pipedrive accounts
     */
    public function pipedriveAccounts()
    {
        return $this->hasMany(PipedriveAccount::class);
    }

    /**
     * Pipelines
     */
    public function pipelines()
    {
        return $this->hasMany(Pipeline::class);
    }

    /**
     * Products
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Prospects
     */
    public function prospects()
    {
        return $this->hasMany(Prospect::class);
    }

    /**
     * Questionnaires
     */
    public function questionnaires()
    {
        return $this->hasMany(Questionnaire::class);
    }

    /**
     * Roles
     */
    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    /**
     * Sms templates
     */
    public function smsTemplates()
    {
        return $this->hasMany(SmsTemplate::class);
    }

    /**
     * Stat chart
     */
    public function statCharts()
    {
        return $this->hasMany(StatChart::class);
    }

    /**
     * Prospects
     */
    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    /**
     * Users
     */
    public function users()
    {
        return $this
            ->belongsToMany(User::class, 'user_project')
            ->withPivot('relevance_event')
            ->withPivot('relevance_prospect')
            ->withPivot('relevance_order_action')
            ->withPivot('relevance_message')
            ->withPivot('prospects_count');
    }

    /**
     * User setting
     */
    public function userSettings()
    {
        return $this->hasMany(UserSetting::class);
    }

    /**
     * Vehicles
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
