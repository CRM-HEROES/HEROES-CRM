<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use HasFactory, SoftDeletes;

    public const ACTIVE = 'active';
    public const DISABLED = 'disabled';

    public const READ_ONLY = 'read_only';
    public const PROPOSE = 'propose';
    public const AUTO = 'auto';

    public const AUTONOMIES = [self::READ_ONLY, self::PROPOSE, self::AUTO];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'project_id',
        'name',
        'description',
        'specialty',
        'persona',
        'instructions',
        'objectives',
        'model',
        'effort',
        'autonomy',
        'settings',
        'status',
        'active_version_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
    ];


    // Relationships

    /**
     * Owner: the agent acts with the rights of this user, never more
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Project (null: every project of the owner)
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function versions()
    {
        return $this->hasMany(AgentVersion::class);
    }

    /**
     * Version used by the new runs
     */
    public function activeVersion()
    {
        return $this->belongsTo(AgentVersion::class, 'active_version_id');
    }

    public function conversations()
    {
        return $this->hasMany(AgentConversation::class);
    }

    public function runs()
    {
        return $this->hasMany(AgentRun::class);
    }

    public function memories()
    {
        return $this->hasMany(AgentMemory::class);
    }

    public function schedules()
    {
        return $this->hasMany(AgentSchedule::class);
    }


    // Helpers

    /**
     * Keys of the skills of the agent
     *
     * @return string[]
     */
    public function skillKeys(): array
    {
        return \Illuminate\Support\Facades\DB::table('agent_skills')
            ->where('agent_id', $this->id)
            ->orderBy('skill_key')
            ->pluck('skill_key')
            ->all();
    }

    /**
     * Tool keys by access ("allow" | "deny")
     *
     * @return array<string, string>
     */
    public function toolGrants(): array
    {
        return \Illuminate\Support\Facades\DB::table('agent_tool_grants')
            ->where('agent_id', $this->id)
            ->pluck('access', 'tool_key')
            ->all();
    }

    /**
     * Can the agent be used in this project?
     */
    public function usableIn(int $projectId): bool
    {
        return $this->status === self::ACTIVE
            && ($this->project_id === null || (int) $this->project_id === $projectId);
    }
}
