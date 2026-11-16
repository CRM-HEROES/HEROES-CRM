<?php

namespace App\Calls\Voice;

use App\Models\CallAttempt;
use App\Models\Prospect;
use Illuminate\Support\Str;

class FakeVoiceProvider implements VoiceProvider
{
    public function start(CallAttempt $attempt, Prospect $prospect, array $context = []): array
    {
        $outcome = config('calls.fake_outcome', 'no_answer');

        if (!in_array($outcome, ['interested', 'not_interested', 'transferred', 'refused', 'callback', 'no_answer'], true)) {
            throw new \UnexpectedValueException('Résultat du fournisseur vocal simulé non pris en charge.');
        }

        return [
            'external_id' => 'fake-' . Str::uuid(),
            'outcome' => $outcome,
            'summary' => 'Appel simulé ; aucun fournisseur vocal réel n’a été contacté.',
            'duration_seconds' => 0,
        ];
    }
}
