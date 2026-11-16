<?php

namespace App\Calls\Voice;

use App\Models\Agent;
use App\Models\CallAttempt;
use App\Models\Line;
use App\Models\Project;
use App\Models\Prospect;
use App\Services\CloudTalk;
use App\Support\PhoneNumber;
use RuntimeException;

class CloudTalkProvider implements VoiceProvider
{
    public function __construct(protected CloudTalk $cloudTalk)
    {
    }

    public function start(CallAttempt $attempt, Prospect $prospect, array $context = []): array
    {
        $agent = Agent::query()->find($attempt->agent_id);
        $project = Project::withoutGlobalScopes()->find($attempt->project_id);

        if (!$agent || !$project) {
            throw new RuntimeException('L’agent ou le projet associé à cet appel est introuvable.');
        }

        $line = Line::query()
            ->where('project_id', $project->id)
            ->where('operator', 'cloudtalk')
            ->where('user_id', $agent->user_id)
            ->first();

        if (!$line) {
            throw new RuntimeException('Aucune ligne CloudTalk n’est associée à l’utilisateur propriétaire de cet agent. Affectez-lui une ligne CloudTalk dans le projet.');
        }

        $config = (array) $line->config;
        if (empty($config['agent_id'])) {
            throw new RuntimeException('Aucun agent CloudTalk n’est configuré sur la ligne de cet utilisateur. Choisissez son agent CloudTalk dans les réglages de la ligne.');
        }

        $rawNumber = trim((string) ($prospect->mobile_phone_number ?: $prospect->phone_number));
        $number = collect(PhoneNumber::candidates($rawNumber))
            ->first(fn ($candidate) => !str_starts_with($candidate, '0'));
        if (!$number) {
            throw new RuntimeException('Le numéro du prospect ne peut pas être converti au format international attendu par CloudTalk.');
        }

        $response = $this->cloudTalk->makeCall($line, $this->cloudTalk->formatCalleeNumber('+' . $number));

        return [
            'external_id' => $this->cloudTalk->callId($response),
            'outcome' => 'pending',
            'summary' => 'Appel CloudTalk lancé ; l’issue sera enregistrée à la réception du webhook.',
            'duration_seconds' => null,
        ];
    }
}
