<?php

namespace App\Models;

use App\Filters\Filters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Export extends Model
{
    use HasFactory;

    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'filters',
        'fields',
        'sort_by',
        'sort_order',
        'count',
        'email',
        'creator_id',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'filters' => 'json',
        'fields' => 'json',
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
     * Creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
