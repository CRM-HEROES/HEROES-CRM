<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageAttachment extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        'name',
        'path',
        'size',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'path',
        'message'
    ];


    /**
     * 
     */
    protected $appends = [
        'url',
        'thumbnail',
    ];


    // Attributes

    /**
     * Get url
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return route('api.project.prospect.thread.message.attachment.show', [
            'project' => $this->message->thread->project->slug, 
            'prospect' => $this->message->prospect->id, 
            'thread' => $this->message->thread->id, 
            'message' => $this->message->id, 
            'attachment' => $this->id
        ]);
    }
    
    /**
     * Get thumbnail
     *
     * @return string
     */
    public function getThumbnailAttribute()
    {
        return route('api.project.prospect.thread.message.attachment.thumbnail', [
            'project' => $this->message->thread->project->slug, 
            'prospect' => $this->message->prospect->id, 
            'thread' => $this->message->thread->id, 
            'message' => $this->message->id, 
            'attachment' => $this->id
        ]);
    }


    // Relationships

    /**
     * Message
     */
    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
