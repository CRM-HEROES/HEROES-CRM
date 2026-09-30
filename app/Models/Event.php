<?php

namespace App\Models;

use App\Filters\Filters;
use App\Models\Scopes\EventScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;

class Event extends Model
{
    use HasFactory, SoftDeletes, RevisionableTrait;
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // static::addGlobalScope(new EventScope());
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'calendar_id',
        'confirmed',
        'creator_id',
        'description',
        'done',
        'drop_off_location',
        'ended_at',
        'latitude',
        'location',
        'send_email_to_prospect',
        'longitude',
        'meta',
        'name',
        'prospect_id',
        'order_id',
        'started_at',
        'user_id',
        'vehicle_id',
        'valid_address',
    ];

    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'meta' => 'json',
    ];


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
     * Calendar
     */
    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }

    /**
     * Confirmed by
     */
    public function confirmedBy()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Done by
     */
    public function doneBy()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Google account
     */
    public function googleAccounts()
    {
        return $this->belongsToMany(GoogleAccount::class, 'google_event')->withPivot('google_event_id', 'hangout');
    }

    /**
     * Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Prospect
     */
    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    /**
     * User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Users
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_event');
    }
    
    /**
     * Vehicle
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

}
