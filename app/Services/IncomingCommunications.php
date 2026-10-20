<?php

namespace App\Services;

use App\Models\Interaction;
use App\Models\Message;
use App\Models\Project;
use App\Models\Sms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class IncomingCommunications
{
    public const TYPES = ['sms' => Sms::class, 'email' => Message::class, 'voicemail' => Interaction::class];

    public static function incoming(Builder $query, string $type): Builder
    {
        if ($type === 'sms') {
            return $query->where(fn ($sms) => $sms->where('is_incoming', true)->orWhere('from_user', false));
        }

        $query->where('from_user', false);
        if ($type === 'email') {
            return $query;
        }

        return $query->where(function ($voicemail) {
            $statuses = ['voicemail', 'voice_mail', 'voice mail', 'messagerie'];
            $voicemail->whereIn('status', $statuses);
            foreach (['outcome', 'cloudtalk_call->outcome', 'cloudtalk_call->status', 'cloudtalk_history->Cdr->status'] as $path) {
                $voicemail->orWhereIn('data->' . $path, $statuses);
            }
            foreach ([
                'has_voicemail', 'is_voicemail', 'voicemail',
                'cloudtalk_ended->data->is_voicemail',
                'cloudtalk_call->has_voicemail', 'cloudtalk_call->is_voicemail', 'cloudtalk_call->voicemail',
                'cloudtalk_history->Cdr->has_voicemail', 'cloudtalk_history->Cdr->is_voicemail', 'cloudtalk_history->Cdr->voicemail',
            ] as $path) {
                $voicemail->orWhere('data->' . $path, true);
            }
            foreach ([
                'voicemail_link', 'voicemail_url',
                'cloudtalk_call->voicemail_link', 'cloudtalk_call->voicemail_url',
                'cloudtalk_history->Cdr->voicemail_link', 'cloudtalk_history->Cdr->voicemail_url',
            ] as $path) {
                $voicemail->orWhere(fn ($recording) => $recording
                    ->whereNotNull('data->' . $path)->where('data->' . $path, '!=', ''));
            }
        });
    }

    protected function query(Project $project, string $type): Builder
    {
        $model = self::TYPES[$type];
        $query = self::incoming($model::query(), $type)
            ->whereIn('prospect_id', $project->prospects()->select('prospects.id'));

        if ($type === 'email') {
            // Keep private/deleted email threads under their existing visibility rules.
            $query->whereHas('thread', fn ($thread) => $thread->where('project_id', $project->id));
        }

        return $query;
    }

    public function counts(Project $project, int $userId): array
    {
        $counts = [];
        foreach (self::TYPES as $type => $model) {
            $table = (new $model())->getTable();
            $query = $this->query($project, $type)->whereNotExists(function ($read) use ($type, $table, $userId) {
                $read->selectRaw('1')->from('communication_reads')
                    ->where('user_id', $userId)->where('communication_type', $type)
                    ->whereColumn('communication_id', $table . '.id');
            });
            if ($type === 'email') {
                $query->whereNull('archived_at')->whereNotExists(function ($archived) use ($userId) {
                    $archived->selectRaw('1')->from('user_message')->where('user_id', $userId)
                        ->whereColumn('message_id', 'messages.id')->whereNotNull('archived_at');
                });
            }
            $counts[$type] = $query->count();
        }

        return ['count' => array_sum($counts)] + $counts;
    }

    public function markRead(Project $project, int $userId, int $prospectId, string $type, array $ids): int
    {
        $project->prospects()->findOrFail($prospectId);
        $ids = $this->query($project, $type)->where('prospect_id', $prospectId)->whereIn('id', $ids)->pluck('id');
        if ($ids->isEmpty()) {
            return 0;
        }

        $readAt = now();
        return DB::table('communication_reads')->insertOrIgnore($ids->map(fn ($id) => [
            'user_id' => $userId, 'communication_type' => $type,
            'communication_id' => $id, 'read_at' => $readAt,
        ])->all());
    }
}
