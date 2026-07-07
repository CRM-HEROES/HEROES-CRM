<?php

namespace App\Models;

use App\Filters\Filters;
use App\Models\Scopes\ProspectScope;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Notifications\Notifiable;

class Prospect extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable, RevisionableTrait;
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new ProspectScope());
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_name',
        'country',
        'city',
        'creator_id',
        'date_of_birth',
        'email',
        'fax_number',
        'first_name',
        'last_name',
        'ip_address',
        'job_title',
        'latitude',
        'longitude',
        'meta',
        'mobile_phone_number',
        'phone_number',
        'postal_code',
        'processed_at',
        'processed_by',
        'project_id',
        'street',
        'street_bis',
        'state',
        'county',
        'title',
        'valid_address',
        'website_url',
    ];

    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'meta' => 'json',
        'date_of_birth' => 'date',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'full_name',
    ];


    // Attributes

    /**
     * Get prospect address
     * from street, postal code, city and country info
     *
     * @return string
     */
    public function getAddressAttribute()
    {
        $items = [
            $this->street,
            $this->street_bis,
            $this->state,
            $this->county,
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

    /**
     * Get full name
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        $items = [
            $this->first_name,
            $this->last_name,
        ];
        
        // Remove unused space
        $items = array_map(function($item) {
            return trim($item);
        }, $items);

        // Remove empty item
        $items = array_filter($items);

        return implode(' ', $items);
    }

    /**
     * Get full name
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return "";
    }

    /**
     * Check if prospect is processed
     *
     * @return string
     */
    public function getProcessedAttribute()
    {
        return $this->processed_at != null;
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
     * Available events
     */
    public function availableEvents()
    {
        return $this->hasMany(AvailableEvent::class);
    }

    /**
     * Campaigns
     */
    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_prospect');
    }

    /**
     * Groups
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Duplicate
     */
    public function duplicate()
    {
        return $this->belongsTo(Prospect::class, 'duplicate_id');
    }

    /**
     * Duplicating
     */
    public function duplicating()
    {
        return $this->hasMany(Prospect::class, 'duplicate_id');
    }

    /**
     * Events
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Groups
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'prospect_group');
    }

    /**
     * Import
     */
    public function import()
    {
        return $this->belongsTo(Import::class);
    }

    /**
     * Files
     */
    public function files()
    {
        return $this->hasMany(File::class);
    }

    /**
     * Interactions
     */
    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }

    /**
     * Labels
     */
    public function labels()
    {
        return $this->belongsToMany(Label::class, 'prospect_label'); //->whereNull('prospect_label.deleted_at');
    }

    /**
     * Links
     */
    public function links()
    {
        return $this->hasMany(Link::class);
    }

    /**
     * Messages
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Pipedrive accounts
     */
    public function pipedriveAccounts()
    {
        return $this->belongsToMany(PipedriveAccount::class, 'pipedrive_person')->withPivot(['person_id', 'from_pipedrive']);
    }

    /**
     * Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Processed By
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Questionnaires
     */
    public function questionnaires()
    {
        return $this->belongsToMany(Questionnaire::class, 'prospect_questionnaire')->withPivot('question_id');
    }

    /**
     * Responses
     */
    public function responses()
    {
        return $this->hasMany(ProspectQuestionnaireResponse::class);
    }

    /**
     * Sms
     */
    public function sms()
    {
        return $this->hasMany(Sms::class);
    }

    /**
     * Users
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'prospect_user');
    }
}
