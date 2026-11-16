<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentMessageAttachment extends Model
{
    protected $fillable = [
        'message_id',
        'name',
        'mime',
        'path',
        'size',
        'summary',
    ];

    protected $casts = [
        'summary' => 'array',
    ];

    public function message()
    {
        return $this->belongsTo(AgentMessage::class, 'message_id');
    }
}
