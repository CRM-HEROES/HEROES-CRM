<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Audit log of a run: every model call and every tool call.
 *
 * It keeps identifiers, counters and summaries, not the raw CRM data that was
 * sent to the model.
 */
class AgentRunStep extends Model
{
    public const MODEL_CALL = 'model_call';
    public const TOOL_CALL = 'tool_call';

    public const RUNNING = 'running';
    public const COMPLETED = 'completed';
    public const FAILED = 'failed';
    public const DENIED = 'denied';
    public const PENDING_APPROVAL = 'pending_approval';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'run_id',
        'seq',
        'type',
        'tool_key',
        'tool_use_id',
        'status',
        'permission_decision',
        'input',
        'output_summary',
        'rows',
        'input_tokens',
        'output_tokens',
        'duration_ms',
        'error',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'input' => 'array',
    ];

    public function run()
    {
        return $this->belongsTo(AgentRun::class, 'run_id');
    }
}
