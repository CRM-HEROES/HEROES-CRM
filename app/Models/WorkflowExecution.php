<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowExecution extends Model
{
    public const PENDING = 'pending';
    public const RUNNING = 'running';
    public const WAITING = 'waiting';
    public const COMPLETED = 'completed';
    public const FAILED = 'failed';
    public const CANCELLED = 'cancelled';
    public const SKIPPED = 'skipped';

    /**
     * Statuses of an execution that is over.
     */
    public const FINISHED = [self::COMPLETED, self::FAILED, self::CANCELLED, self::SKIPPED];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'workflow_id',
        'workflow_version_id',
        'subject_type',
        'subject_id',
        'status',
        'current_node_id',
        'resume_at',
        'locked_at',
        'waiting_event',
        'trigger_key',
        'context',
        'error',
        'started_at',
        'finished_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'context' => 'array',
        'resume_at' => 'datetime',
        'locked_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];


    // Relationships

    /**
     * Workflow
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Version the execution is pinned to
     */
    public function version()
    {
        return $this->belongsTo(WorkflowVersion::class, 'workflow_version_id');
    }

    /**
     * Steps of the execution (the execution log)
     */
    public function nodes()
    {
        return $this->hasMany(WorkflowNodeExecution::class, 'execution_id');
    }
}
