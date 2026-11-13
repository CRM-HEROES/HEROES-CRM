<?php

namespace App\Services;

use App\Models\Interaction;
use App\Models\Message;
use App\Models\Project;
use App\Models\Sms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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

    protected function unreadQuery(Project $project, string $type, int $userId): Builder
    {
        return self::excludeRead($this->query($project, $type), $type, $userId);
    }

    public static function excludeRead(Builder $query, string $type, int $userId): Builder
    {
        $model = self::TYPES[$type];
        $table = (new $model())->getTable();
        $query->whereNotExists(function ($read) use ($type, $table, $userId) {
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

        return $query;
    }

    public function appendUnread(Collection $prospects, Project $project, int $userId): void
    {
        if ($prospects->isEmpty()) {
            return;
        }

        // Load only the current page, with one query per communication type.
        $unread = [];
        foreach (self::TYPES as $type => $model) {
            $unread[$type] = $this->unreadQuery($project, $type, $userId)
                ->whereIn('prospect_id', $prospects->pluck('id'))
                ->get(['id', 'prospect_id'])->groupBy('prospect_id');
        }

        foreach ($prospects as $prospect) {
            $ids = [];
            foreach (self::TYPES as $type => $model) {
                $ids[$type] = $unread[$type]->get($prospect->id, collect())->pluck('id')->values()->all();
            }
            $prospect->unread_communications = $ids;
        }
    }

    public function counts(Project $project, int $userId): array
    {
        $counts = [];
        foreach (self::TYPES as $type => $model) {
            $query = $this->unreadQuery($project, $type, $userId);
            $counts[$type] = $type === 'sms'
                ? $query->distinct()->count('prospect_id')
                : $query->count();
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

    /**
     * Process history of the incoming communications of a prospect (SMS or
     * email): who processed it and when.
     */
    public function readHistory(Project $project, int $prospectId, string $type = 'sms'): Collection
    {
        $project->prospects()->findOrFail($prospectId);

        if (!in_array($type, ['sms', 'email'], true)) {
            $type = 'sms';
        }

        $model = self::TYPES[$type];
        $table = (new $model())->getTable();

        return DB::table('communication_reads')
            ->join($table, $table . '.id', '=', 'communication_reads.communication_id')
            ->leftJoin('users', 'users.id', '=', 'communication_reads.user_id')
            ->where('communication_reads.communication_type', $type)
            ->where($table . '.prospect_id', $prospectId)
            ->when($type === 'sms', fn ($incoming) => $incoming->where(function ($sms) {
                $sms->where('sms.is_incoming', true)
                    ->orWhere('sms.from_user', false);
            }))
            ->when($type === 'email', fn ($incoming) => $incoming->where('messages.from_user', false))
            ->orderByDesc('communication_reads.read_at')
            ->get([
                $table . '.id',
                $type === 'email' ? 'messages.subject as message' : 'sms.message',
                'communication_reads.user_id',
                'communication_reads.read_at',
                'users.name as user_name',
            ]);
    }

    public function smsReadHistory(Project $project, int $prospectId): Collection
    {
        return $this->readHistory($project, $prospectId, 'sms');
    }
}
