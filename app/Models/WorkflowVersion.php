<?php

namespace App\Models;

use App\Workflows\WorkflowTriggerResolver;
use Illuminate\Database\Eloquent\Model;

/**
 * Published graph of a workflow. Immutable once created:
 * editing a workflow publishes a new version.
 */
class WorkflowVersion extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'workflow_id',
        'version',
        'graph',
        'trigger_type',
        'baselined_at',
        'creator_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'graph' => 'array',
        'baselined_at' => 'datetime',
    ];

    /**
     * The trigger index is cached: any change of a version refreshes it.
     */
    protected static function booted(): void
    {
        static::saved(fn () => WorkflowTriggerResolver::forgetIndex());
    }


    // Relationships

    /**
     * Workflow
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }
}
