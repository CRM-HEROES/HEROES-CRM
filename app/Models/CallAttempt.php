<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallAttempt extends Model
{
    public const QUEUED = 'queued';
    public const SKIPPED = 'skipped';
    public const CALLING = 'calling';
    public const COMPLETED = 'completed';
    public const FAILED = 'failed';
    public const TRANSFERRED = 'transferred';
    public const REFUSED = 'refused';

    protected $fillable = [
        'prospect_id',
        'agent_id',
        'project_id',
        'agent_run_id',
        'provider',
        'external_id',
        'status',
        'skip_reason',
        'outcome',
        'summary',
        'duration_seconds',
        'scheduled_at',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];
}
