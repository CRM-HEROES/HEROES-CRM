<?php

namespace App\Calls;

use App\Models\CallAttempt;
use App\Models\Calendar;
use App\Models\Event;
use App\Models\Label;
use App\Models\Interaction;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\User;
use App\Notifications\CallBackScheduled;

class CallCompletion
{
    public function __construct(protected WorkingHours $workingHours)
    {
    }

    public function complete(CallAttempt $attempt, Prospect $prospect, Project $project, User $owner, array $result): void
    {
        $status = ($result['outcome'] === 'transferred')
            ? CallAttempt::TRANSFERRED
            : ($result['outcome'] === 'refused' ? CallAttempt::REFUSED : CallAttempt::COMPLETED);

        $attempt->update([
            'external_id' => $result['external_id'],
            'status' => $status,
            'outcome' => $result['outcome'],
            'summary' => $result['summary'],
            'duration_seconds' => $result['duration_seconds'],
            'ended_at' => now(),
        ]);
        $this->recordInteraction($attempt, $prospect, $owner, $result);

        if (in_array($result['outcome'], ['no_answer', 'callback'], true)) {
            $this->scheduleFollowUp(
                $prospect,
                $project,
                $owner,
                (bool) ($result['immediate_callback'] ?? false),
                (string) ($result['summary'] ?? 'Rappel demandé.')
            );
        }
    }

    protected function recordInteraction(CallAttempt $attempt, Prospect $prospect, User $owner, array $result): void
    {
        $interaction = $attempt->external_id
            ? Interaction::query()
                ->where('prospect_id', $prospect->id)
                ->where('source', $attempt->provider === 'cloudtalk' ? 'cloudtalk' : $attempt->provider)
                ->where('data->call_id', $attempt->external_id)
                ->first()
            : null;

        $interaction ??= $prospect->interactions()->make([
            'source' => $attempt->provider === 'cloudtalk' ? 'cloudtalk' : $attempt->provider,
            'creator_id' => $owner->id,
            'from_user' => true,
        ]);
        $data = (array) ($interaction->data ?? []);
        $data['outcome'] = $result['outcome'];
        $data['note'] = $result['summary'];
        $data['call_attempt_id'] = $attempt->id;
        if ($attempt->external_id) {
            $data['call_id'] = $attempt->external_id;
        }
        $interaction->data = $data;
        $interaction->status = $result['outcome'];
        $interaction->started_at = $attempt->started_at;
        $interaction->ended_at = now();
        $interaction->save();
    }

    protected function scheduleFollowUp(Prospect $prospect, Project $project, User $owner, bool $immediate, string $reason): void
    {
        $labelId = $project->call_back_label_id;
        $label = $labelId
            ? Label::query()->whereKey($labelId)
                ->whereHas('category', fn ($query) => $query->where('project_id', $project->id)->where('for', 'prospect'))
                ->first()
            : null;
        if (!$label) {
            throw new \RuntimeException('Le label « à rappeler » du projet n’est pas configuré.');
        }
        $prospect->labels()->syncWithoutDetaching([$label->id]);

        $calendar = Calendar::withoutGlobalScopes()->where('project_id', $project->id)->first();
        if (!$calendar) {
            throw new \RuntimeException('Aucun calendrier n’est configuré pour le rappel.');
        }
        $after = $immediate ? now() : now()->addHours((int) config('calls.callback_delay_hours', 48));
        $start = $this->workingHours->nextOpening($prospect, $after);
        Event::create([
            'calendar_id' => $calendar->id,
            'prospect_id' => $prospect->id,
            'user_id' => $project->call_transfer_user_id ?: $owner->id,
            'creator_id' => $owner->id,
            'name' => 'Rappel demandé',
            'description' => $reason,
            'started_at' => $start,
            'ended_at' => $start->copy()->addMinutes(30),
        ]);

        $adviser = User::query()->find($project->call_transfer_user_id) ?: $owner;
        $adviser->notify(new CallBackScheduled($prospect, $start->toIso8601String(), $reason));
    }
}
