<?php

namespace App\Models;

use App\Workflows\WorkflowTriggerResolver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model
{
    use HasFactory, SoftDeletes;

    public const DRAFT = 'draft';
    public const ACTIVE = 'active';
    public const PAUSED = 'paused';
    public const ARCHIVED = 'archived';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'legacy_campaign_id',
        'name',
        'description',
        'draft_graph',
        'status',
        'active_version_id',
        'creator_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'draft_graph' => 'array',
    ];

    /**
     * The trigger index is cached: any change of a workflow refreshes it.
     */
    protected static function booted(): void
    {
        static::saved(fn () => WorkflowTriggerResolver::forgetIndex());
        static::deleted(fn () => WorkflowTriggerResolver::forgetIndex());
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
     * Versions
     */
    public function versions()
    {
        return $this->hasMany(WorkflowVersion::class);
    }

    /**
     * Version used by the new executions
     */
    public function activeVersion()
    {
        return $this->belongsTo(WorkflowVersion::class, 'active_version_id');
    }

    /**
     * Executions
     */
    public function executions()
    {
        return $this->hasMany(WorkflowExecution::class);
    }
}
