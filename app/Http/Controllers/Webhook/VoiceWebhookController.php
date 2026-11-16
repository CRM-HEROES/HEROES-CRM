<?php

namespace App\Http\Controllers\Webhook;

use App\Calls\CallCompletion;
use App\Calls\WorkingHours;
use App\Models\Agent;
use App\Models\AgentDocument;
use App\Models\CallAttempt;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\User;
use App\Utils\ProjectSetting;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VoiceWebhookController
{
    public function tool(Request $request, CallAttempt $attempt, WorkingHours $workingHours, CallCompletion $completion)
    {
        $this->verify($request, $attempt);
        $tool = (string) $request->input('tool');

        return match ($tool) {
            'transfer' => $this->transfer($attempt, $workingHours),
            'search_docs' => $this->searchDocs($attempt, (string) $request->input('query', '')),
            'refuse' => $this->refuse($attempt, $completion),
            default => throw new HttpException(422, 'Outil voix inconnu.'),
        };
    }

    public function ended(Request $request, CallAttempt $attempt, CallCompletion $completion)
    {
        $this->verify($request, $attempt);
        if ($attempt->status !== CallAttempt::CALLING) {
            return response(['status' => 'ignored'], 200);
        }

        $project = Project::withoutGlobalScopes()->findOrFail($attempt->project_id);
        $prospect = Prospect::withoutGlobalScopes()->findOrFail($attempt->prospect_id);
        $agent = Agent::query()->findOrFail($attempt->agent_id);
        $owner = User::query()->findOrFail($agent->user_id);
        $outcome = (string) $request->input('outcome', 'no_answer');
        abort_unless(in_array($outcome, ['answered', 'no_answer', 'busy', 'voicemail', 'interested', 'not_interested', 'callback', 'refused', 'transferred'], true), 422);

        $completion->complete($attempt, $prospect, $project, $owner, [
            'external_id' => $attempt->external_id,
            'outcome' => $outcome,
            'summary' => mb_substr((string) $request->input('summary', 'Issue reçue depuis le webhook voix.'), 0, 4000),
            'duration_seconds' => max(0, (int) $request->input('duration_seconds', 0)),
        ]);

        return ['status' => $attempt->fresh()->status];
    }

    protected function transfer(CallAttempt $attempt, WorkingHours $workingHours): array
    {
        $project = Project::withoutGlobalScopes()->findOrFail($attempt->project_id);
        $prospect = Prospect::withoutGlobalScopes()->findOrFail($attempt->prospect_id);
        $settings = ProjectSetting::get($project, 'calls', []);
        $manualAvailable = (bool) (is_object($settings) ? ($settings->transfer_available ?? false) : ($settings['transfer_available'] ?? false));
        $adviser = $project->call_transfer_user_id ? User::query()->find($project->call_transfer_user_id) : null;

        if (!$manualAvailable || !$adviser || !$workingHours->isOpen($prospect)) {
            return ['available' => false, 'reason' => 'Aucun conseiller disponible pendant les heures ouvrées.'];
        }

        return [
            'available' => false,
            'adviser_id' => $adviser->id,
            'reason' => 'Le fournisseur vocal configuré ne permet pas de réaliser un transfert réel.',
        ];
    }

    protected function searchDocs(CallAttempt $attempt, string $query): array
    {
        $documents = AgentDocument::query()->where('agent_id', $attempt->agent_id)->where('status', 'ready')->get(['name', 'summary']);
        $terms = array_filter(preg_split('/\s+/', mb_strtolower($query)) ?: []);
        $matches = $documents->filter(function (AgentDocument $document) use ($terms) {
            $text = mb_strtolower($document->name . ' ' . json_encode($document->summary, JSON_UNESCAPED_UNICODE));
            return !$terms || collect($terms)->contains(fn ($term) => str_contains($text, $term));
        })->take(5)->map(fn (AgentDocument $document) => [
            'document' => $document->name,
            'summary' => $document->summary,
            'untrusted' => true,
        ])->values()->all();

        return ['documents' => $matches];
    }

    protected function refuse(CallAttempt $attempt, CallCompletion $completion): array
    {
        $prospect = Prospect::withoutGlobalScopes()->findOrFail($attempt->prospect_id);
        $project = Project::withoutGlobalScopes()->findOrFail($attempt->project_id);
        $agent = Agent::query()->findOrFail($attempt->agent_id);
        $owner = User::query()->findOrFail($agent->user_id);
        $prospect->forceFill(['do_not_call' => true])->save();
        $completion->complete($attempt, $prospect, $project, $owner, [
            'external_id' => $attempt->external_id,
            'outcome' => 'refused',
            'summary' => 'Le prospect a demandé à ne plus être appelé. Des excuses ont été présentées.',
            'duration_seconds' => $attempt->duration_seconds,
        ]);

        return ['status' => CallAttempt::REFUSED];
    }

    protected function verify(Request $request, CallAttempt $attempt): void
    {
        $project = Project::withoutGlobalScopes()->findOrFail($attempt->project_id);
        $settings = ProjectSetting::get($project, 'calls', []);
        $secret = is_object($settings) ? ($settings->ai_voice_webhook_secret ?? $settings->webhook_secret ?? null) : ($settings['ai_voice_webhook_secret'] ?? $settings['webhook_secret'] ?? null);
        $secret = $secret ?: config('calls.ai_voice.webhook_secret');
        abort_unless(is_string($secret) && $secret !== '', 503, 'Le secret webhook voix IA n’est pas configuré.');

        $provided = preg_replace('/^sha256=/i', '', (string) $request->header('X-Voice-Signature', ''));
        $expected = hash_hmac('sha256', $request->getContent(), $secret);
        abort_unless($provided !== '' && hash_equals($expected, $provided), 401, 'Signature webhook voix IA invalide.');
    }
}
