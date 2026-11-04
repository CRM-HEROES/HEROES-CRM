<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * What an agent remembers about its user. Always visible and editable by the
 * user; CRM data is never kept here (it is read again from the CRM).
 */
class AgentMemory extends Model
{
    public const PREFERENCE = 'preference';
    public const FACT = 'fact';
    public const DECISION = 'decision';

    public const FROM_USER = 'user';
    public const FROM_AGENT = 'agent';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agent_id',
        'user_id',
        'project_id',
        'kind',
        'content',
        'origin',
        'source_run_id',
        'pinned',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'pinned' => 'boolean',
        'expires_at' => 'datetime',
    ];

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
}
