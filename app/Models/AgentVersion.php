<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Immutable snapshot of an agent configuration: a run is pinned to one of them.
 */
class AgentVersion extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agent_id',
        'version',
        'config',
        'creator_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'config' => 'array',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
