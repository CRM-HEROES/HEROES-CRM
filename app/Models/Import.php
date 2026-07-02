<?php

namespace App\Models;

use App\Filters\Filters;
use App\Models\Scopes\ImportScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    use HasFactory;
    
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new ImportScope());
    }
    
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cols_count',
        'creator_id',
        'field_delimiter',
        'field_enclosure',
        'duplicates_fields',
        'groups',
        'headers',
        'is_processing',
        'labels',
        'mapping',
        'name',
        'path',
        'projects',
        'processing_at',
        'processed_at',
        'rows_count',
        'source',
        'token',
        'users',
        'values',
    ];
    

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'path',
    ];


    /**
     * 
     */
    protected $appends = [
        'url',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'duplicates_fields' => 'json',
        'groups'    => 'json',
        'headers'   => 'json',
        'labels'    => 'json',
        'mapping'   => 'json',
        'projects'  => 'json',
        'prospects' => 'json',
        'users'     => 'json',
        'values'    => 'json',
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


    // Attributes

    /**
     * Get font url
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return $this->project ? route('api.project.import.download', [
            'project' => $this->project->slug, 
            'import' => $this->id
        ]) : null;
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
    
    /**
     * Prospects
     */
    public function prospects()
    {
        return $this->hasMany(Prospect::class);
    }

}
