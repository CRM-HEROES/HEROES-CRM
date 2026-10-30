<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowNodeExecution extends Model
{
    public const RUNNING = 'running';
    // A "wait for an event" node that is parked, waiting for its event or its timeout
    public const WAITING = 'waiting';
    public const COMPLETED = 'completed';
    public const FAILED = 'failed';
    public const SKIPPED = 'skipped';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'execution_id',
        'node_id',
        'node_type',
        'status',
        'branch',
        'attempts',
        'result',
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
        'result' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];


    // Relationships

    /**
     * Execution
     */
    public function execution()
    {
        return $this->belongsTo(WorkflowExecution::class, 'execution_id');
    }
}
