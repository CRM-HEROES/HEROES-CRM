<?php

namespace App\Models;

use App\Filters\Filters;
use App\Models\Scopes\CategoryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;
    
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new CategoryScope());
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        'description',
        'name',
        'for',
        'color',
        'bgcolor',
        'unique',
        'order',
    ];


    // Relationships

    /**
     * Labels
     */
    public function labels()
    {
        return $this->hasMany(Label::class);
    }

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
        return $this->belongsToMany(Role::class, 'role_category');
    }
    
    /**
     * Users
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_category');
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
}
