<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interaction extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'path',
        'creator_id',
        'data',
        'ended_at',
        'from_user',
        'number',
        'from_number',
        'size',
        'source',
        'status',
        'started_at',
    ];
    

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'size',
        'path',
    ];


    /**
     * 
     */
    protected $appends = [
        'audio',
        'duration',
    ];


    // Attributes

    /**
     * Get font url
     *
     * @return string
     */
    public function getAudioAttribute()
    {
        if (!$this->path && !$this->hasCloudTalkRecording()) {
            return null;
        }

        return route('api.project.prospect.interaction.audio', [
            'project' => $this->prospect->project->slug, 
            'prospect' => $this->prospect->id, 
            'interaction' => $this->id
        ]);
    }

    public function getDurationAttribute(): ?int
    {
        if (!$this->started_at || !$this->ended_at) {
            return null;
        }

        try {
            $startedAt = Carbon::parse($this->started_at);
            $endedAt = Carbon::parse($this->ended_at);
        } catch (\Throwable $e) {
            return null;
        }

        $duration = $endedAt->getTimestamp() - $startedAt->getTimestamp();

        return $duration >= 0 ? $duration : null;
    }

    public function cloudTalkCallId(): ?string
    {
        if ($this->source !== 'cloudtalk' || !is_array($this->data)) {
            return null;
        }

        $callId = data_get($this->data, 'call_id')
            ?: data_get($this->data, 'cdr_id')
            ?: data_get($this->data, 'cloudtalk_call.id')
            ?: data_get($this->data, 'cloudtalk_history.Cdr.id')
            ?: data_get($this->data, 'id');

        if (!$callId || !preg_match('/^\d+$/', (string) $callId)) {
            return null;
        }

        return (string) $callId;
    }

    protected function hasCloudTalkRecording(): bool
    {
        if ($this->source !== 'cloudtalk' || !$this->cloudTalkCallId()) {
            return false;
        }

        $recorded = data_get($this->data, 'recorded');
        $recorded ??= data_get($this->data, 'cloudtalk_call.recorded');
        $recorded ??= data_get($this->data, 'cloudtalk_history.Cdr.recorded');

        if ($recorded !== null) {
            return filter_var($recorded, FILTER_VALIDATE_BOOLEAN);
        }

        return (bool) (
            data_get($this->data, 'recording_url') ||
            data_get($this->data, 'cloudtalk_call.recording_url') ||
            data_get($this->data, 'cloudtalk_history.Cdr.recording_link')
        );
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'json',
    ];


    // Relationships

    /**
     * Groups
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Prospect
     */
    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }
}
