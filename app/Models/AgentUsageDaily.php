<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentUsageDaily extends Model
{
    protected $table = 'agent_usage_daily';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'project_id',
        'date',
        'runs',
        'input_tokens',
        'output_tokens',
        'cache_read_tokens',
        'cost_micro',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
