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
        'call_direction',
        'call_outcome',
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

    public function getCallDirectionAttribute(): ?string
    {
        if ($this->source === 'cloudtalk' && is_array($this->data)) {
            $direction = data_get($this->data, 'direction')
                ?: data_get($this->data, 'cloudtalk_call.direction')
                ?: data_get($this->data, 'cloudtalk_call.type')
                ?: data_get($this->data, 'cloudtalk_history.Cdr.direction')
                ?: data_get($this->data, 'cloudtalk_history.Cdr.type');

            $direction = $this->normalizeCallDirection($direction);

            if ($direction) {
                return $direction;
            }
        }

        if ($this->from_user !== null) {
            return $this->from_user ? 'outgoing' : 'incoming';
        }

        return null;
    }

    public function getCallOutcomeAttribute(): ?string
    {
        if ($this->source === 'cloudtalk' && is_array($this->data)) {
            if ($this->hasCloudTalkVoicemail()) {
                return 'voicemail';
            }

            $outcome = data_get($this->data, 'outcome')
                ?: data_get($this->data, 'cloudtalk_call.outcome')
                ?: data_get($this->data, 'cloudtalk_call.status')
                ?: data_get($this->data, 'cloudtalk_history.Cdr.status')
                ?: $this->status;

            $outcome = $this->normalizeCallOutcome($outcome);

            if ($outcome) {
                return $outcome;
            }
        }

        return $this->normalizeCallOutcome($this->status);
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

    protected function hasCloudTalkVoicemail(): bool
    {
        if ($this->source !== 'cloudtalk' || !is_array($this->data)) {
            return false;
        }

        foreach ([
            data_get($this->data, 'has_voicemail'),
            data_get($this->data, 'is_voicemail'),
            data_get($this->data, 'voicemail'),
            data_get($this->data, 'cloudtalk_ended.data.is_voicemail'),
            data_get($this->data, 'cloudtalk_call.has_voicemail'),
            data_get($this->data, 'cloudtalk_call.is_voicemail'),
            data_get($this->data, 'cloudtalk_call.voicemail'),
            data_get($this->data, 'cloudtalk_history.Cdr.has_voicemail'),
            data_get($this->data, 'cloudtalk_history.Cdr.is_voicemail'),
            data_get($this->data, 'cloudtalk_history.Cdr.voicemail'),
        ] as $value) {
            if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                return true;
            }
        }

        return (bool) (
            data_get($this->data, 'voicemail_link') ||
            data_get($this->data, 'voicemail_url') ||
            data_get($this->data, 'cloudtalk_call.voicemail_link') ||
            data_get($this->data, 'cloudtalk_call.voicemail_url') ||
            data_get($this->data, 'cloudtalk_history.Cdr.voicemail_link') ||
            data_get($this->data, 'cloudtalk_history.Cdr.voicemail_url') ||
            $this->containsAny(
                strtolower((string) data_get($this->data, 'cloudtalk_history.Cdr.status')),
                ['voicemail', 'voice_mail', 'voice mail']
            )
        );
    }

    protected function normalizeCallDirection(?string $direction): ?string
    {
        return match (strtolower((string) $direction)) {
            'inbound', 'incoming', 'in' => 'incoming',
            'outbound', 'outgoing', 'out' => 'outgoing',
            'internal' => 'internal',
            default => null,
        };
    }

    protected function normalizeCallOutcome(?string $outcome): ?string
    {
        $outcome = strtolower((string) $outcome);

        if ($this->containsAny($outcome, ['voicemail', 'voice_mail', 'voice mail'])) {
            return 'voicemail';
        }

        if ($this->containsAny($outcome, [
            'missed',
            'no_answer',
            'not_answered',
            'unanswered',
            'abandoned',
            'lost',
        ])) {
            return 'missed';
        }

        return null;
    }

    protected function containsAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'json',
        'from_user' => 'boolean',
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
