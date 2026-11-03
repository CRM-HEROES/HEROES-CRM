<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentRun extends Model
{
    public const PENDING = 'pending';
    public const RUNNING = 'running';
    public const WAITING_APPROVAL = 'waiting_approval';
    public const COMPLETED = 'completed';
    public const FAILED = 'failed';
    public const CANCELLED = 'cancelled';

    public const USER = 'user';
    public const SCHEDULE = 'schedule';
    public const WORKFLOW = 'workflow';

    /**
     * Statuses of a run that is over.
     */
    public const FINISHED = [self::COMPLETED, self::FAILED, self::CANCELLED];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id',
        'agent_id',
        'agent_version_id',
        'user_id',
        'project_id',
        'trigger',
        'status',
        'model',
        'iterations',
        'input_tokens',
        'output_tokens',
        'cache_read_tokens',
        'cache_write_tokens',
        'cost_micro',
        'locked_at',
        'started_at',
        'finished_at',
        'error',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'locked_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(AgentConversation::class, 'conversation_id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function version()
    {
        return $this->belongsTo(AgentVersion::class, 'agent_version_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function steps()
    {
        return $this->hasMany(AgentRunStep::class, 'run_id');
    }

    public function actions()
    {
        return $this->hasMany(AgentAction::class, 'run_id');
    }
}
