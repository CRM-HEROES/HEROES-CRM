<?php

namespace App\Services;

use App\Models\Interaction;
use App\Models\Project;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DashboardRecordings
{
    protected ?array $lines = null;

    public function __construct(protected Project $project)
    {
    }

    /**
     * List candidates without making provider requests during dashboard refreshes.
     */
    public function urls(array $userIds, string $from, string $to): array
    {
        $urls = [];
        if (!$userIds) {
            return $urls;
        }

        foreach ($this->candidates($userIds, $from, $to) as $interaction) {
            if (isset($urls[$interaction->creator_id]) || !$this->eligible($interaction)) {
                continue;
            }

            $urls[$interaction->creator_id] = route('api.project.stat-dashboard.recording', [
                'project' => $this->project->slug,
                'userId' => $interaction->creator_id,
                'from' => substr($from, 0, 10),
                'to' => substr($to, 0, 10),
            ]);

            if (count($urls) === count($userIds)) {
                break;
            }
        }

        return $urls;
    }

    /**
     * Resolve availability on playback, falling back within the same user and period.
     */
    public function media(int $userId, string $from, string $to, CloudTalk $cloudTalk): ?array
    {
        foreach ($this->candidates([$userId], $from, $to) as $interaction) {
            if (!$this->eligible($interaction)) {
                continue;
            }

            if ($interaction->source !== 'cloudtalk') {
                $disk = Storage::disk('interactions');

                return ['body' => $disk->get($interaction->path), 'content_type' => $disk->mimeType($interaction->path) ?: 'audio/x-wav'];
            }

            try {
                return $cloudTalk->recordingMedia($this->lines()[$userId], $interaction->cloudTalkCallId());
            } catch (RuntimeException $e) {
                if (!in_array($e->getCode(), [404, 410], true)) {
                    throw $e;
                }

                // Expired/deleted audio must not be offered again on every refresh.
                Cache::put($this->missingKey($interaction), true, now()->addMinutes(10));
            }
        }

        return null;
    }

    protected function candidates(array $userIds, string $from, string $to): iterable
    {
        return Interaction::query()
            ->join('prospects as p', 'p.id', '=', 'interactions.prospect_id')
            ->where('p.project_id', $this->project->id)
            ->whereNull('p.deleted_at')
            ->whereIn('interactions.creator_id', $userIds)
            ->whereBetween(DB::raw('COALESCE(interactions.started_at, interactions.created_at)'), [$from, $to])
            ->where(function ($query) {
                $query->where('interactions.source', 'cloudtalk')
                    ->orWhere(function ($query) {
                        $query->whereNotNull('interactions.path')->where('interactions.path', '!=', '');
                    });
            })
            ->select('interactions.*')
            ->orderByRaw('COALESCE(interactions.started_at, interactions.created_at) DESC')
            ->orderByDesc('interactions.id')
            ->cursor();
    }

    protected function eligible(Interaction $interaction): bool
    {
        if ($interaction->source === 'cloudtalk') {
            return $interaction->hasCloudTalkRecording()
                && isset($this->lines()[$interaction->creator_id])
                && !Cache::get($this->missingKey($interaction), false);
        }

        return $interaction->path && Storage::disk('interactions')->exists($interaction->path);
    }

    protected function lines(): array
    {
        if ($this->lines === null) {
            $this->lines = $this->project->lines()->where('operator', 'cloudtalk')->get()
                ->filter(fn ($line) => filled(data_get($line->config, 'api_key_id'))
                    && filled(data_get($line->config, 'api_key_secret'))
                    && is_numeric(data_get($line->config, 'agent_id')))
                ->keyBy('user_id')->all();
        }

        return $this->lines;
    }

    protected function missingKey(Interaction $interaction): string
    {
        return 'dashboard-recording-missing:' . $this->project->id . ':' . $interaction->id . ':'
            . $interaction->cloudTalkCallId() . ':' . $interaction->getRawOriginal('updated_at');
    }
}
