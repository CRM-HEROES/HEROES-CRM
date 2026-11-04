<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentReport extends Model
{
    public const MARKDOWN = 'markdown';
    public const JSON = 'json';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agent_id',
        'run_id',
        'user_id',
        'project_id',
        'title',
        'format',
        'content',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function run()
    {
        return $this->belongsTo(AgentRun::class, 'run_id');
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
