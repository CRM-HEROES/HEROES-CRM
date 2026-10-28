<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{
    use HasFactory;

    /**
     * Table
     */
    protected $table = "sms";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'created_at',
        'creator_id',
        'from_user',
        'is_incoming',
        'message',
        'media',
        'error',
        'project_id',
        'source',
        'sent_at',
        'cloudtalk_message_id',
        'cloudtalk_event_id',
        'cloudtalk_sender_number',
        'cloudtalk_send_started_at',
    ];
    
    protected $casts = [
        'from_user' => 'boolean',
        'is_incoming' => 'boolean',
        'media' => 'array',
        'sent_at' => 'datetime',
        'cloudtalk_send_started_at' => 'datetime',
    ];

    protected $hidden = [
        'media', 'prospect', 'cloudtalk_message_id', 'cloudtalk_event_id',
        'cloudtalk_sender_number', 'cloudtalk_send_started_at',
    ];

    protected $appends = ['attachments'];

    public function getAttachmentsAttribute(): array
    {
        return collect($this->media ?? [])->map(function (array $media, int $index) {
            $url = isset($media['path'])
                ? route('api.project.prospect.sms.attachment.show', [
                    'project' => $this->prospect->project,
                    'prospect' => $this->prospect_id,
                    'sms' => $this->id,
                    'attachment' => $index,
                ], false)
                : ($media['url'] ?? null);

            return [
                'url' => $url,
                'file_name' => $media['file_name'] ?? 'attachment',
                'mime_type' => $media['mime_type'] ?? null,
                'extension' => $media['extension'] ?? null,
            ];
        })->all();
    }


    // Attributes

    /**
     * Creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Prospect
     */
    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }
}
