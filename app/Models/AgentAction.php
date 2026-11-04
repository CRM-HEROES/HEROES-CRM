<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A write an agent wants to make and that a human has to approve.
 */
class AgentAction extends Model
{
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const EXECUTED = 'executed';
    public const FAILED = 'failed';
    public const EXPIRED = 'expired';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'run_id',
        'agent_id',
        'user_id',
        'project_id',
        'tool_key',
        'tool_use_id',
        'risk',
        'payload',
        'preview',
        'status',
        'decided_by',
        'decided_at',
        'executed_at',
        'result',
        'error',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payload' => 'array',
        'preview' => 'array',
        'result' => 'array',
        'decided_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    public function run()
    {
        return $this->belongsTo(AgentRun::class, 'run_id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
