<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'archived_at',
        'archived_by',
        'body',
        'plain_text',
        'error',
        'sent',
        'creator_id',
        'from_user',
        'thread_id',
    ];


    // Relationships

    /**
     * Attachments
     */
    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }

    /**
     * Prospect
     */
    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Prospect
     */
    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    /**
     * Thread
     */
    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    /**
     * Waiting users
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_message')->withPivot('archived_at');
    }
}
