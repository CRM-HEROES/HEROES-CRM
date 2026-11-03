<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One message of the history, kept exactly as the API returned it.
 *
 * Append only: a stored message is never edited, since the history is
 * replayed unchanged to the model (thinking blocks included) and any edit
 * would invalidate them and the prompt cache.
 */
class AgentMessage extends Model
{
    public const USER = 'user';
    public const ASSISTANT = 'assistant';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id',
        'run_id',
        'role',
        'content',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'content' => 'array',
    ];

    public function conversation()
    {
        return $this->belongsTo(AgentConversation::class, 'conversation_id');
    }

    public function run()
    {
        return $this->belongsTo(AgentRun::class, 'run_id');
    }
}
