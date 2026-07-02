<?php

namespace App\Models;

use App\Filters\Filters;
use App\Models\Scopes\CalendarScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Calendar extends Model
{
    use HasFactory, SoftDeletes;
    

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new CalendarScope());
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bgcolor',
        'color',
        'creator_id',
        'name',
        'type',
        'order',
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
     * Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    /**
     * Roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_calendar');
    }
    
    /**
     * Users
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_calendar');
    }
}
