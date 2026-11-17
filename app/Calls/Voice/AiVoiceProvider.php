<?php

namespace App\Calls\Voice;

use App\Models\CallAttempt;
use App\Models\Project;
use App\Models\Prospect;
use App\Calls\CallSettings;
use RuntimeException;

class AiVoiceProvider implements VoiceProvider
{
    public function start(CallAttempt $attempt, Prospect $prospect, array $context = []): array
    {
        $project = Project::withoutGlobalScopes()->findOrFail($attempt->project_id);
        $settings = CallSettings::forProject($project);
        $firstName = trim((string) $prospect->first_name);
        $company = trim((string) $project->name);
        $announcement = "Bonjour {$firstName}, je suis un assistant virtuel de {$company}.";
        $configuredAnnouncement = $settings['greeting'] ?? $settings['announcement'] ?? null;
        if (is_string($configuredAnnouncement) && trim($configuredAnnouncement) !== '') {
            $configuredAnnouncement = str_replace(
                ['[prénom]', '[société]'],
                [$firstName, $company],
                trim($configuredAnnouncement)
            );
            if (stripos($configuredAnnouncement, 'assistant virtuel') === false) {
                $configuredAnnouncement = $announcement . ' ' . $configuredAnnouncement;
            }
            $announcement = $configuredAnnouncement;
        }

        $script = trim((string) ($context['script'] ?? ''));
        if ($script === '') {
            $script = implode("\n", array_filter([
                implode("\n", (array) ($context['points_a_dire'] ?? [])),
                implode("\n", (array) ($context['objections_probables'] ?? [])),
            ]));
        }

        $context['ai_voice_script'] = trim($announcement . "\n" . $script);
        $context['ai_voice_provider'] = 'skeleton';
        $attempt->update([
            'summary' => json_encode($context, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
        ]);

        throw new RuntimeException('Le fournisseur de voix IA est un squelette sans fournisseur réel : aucun appel n’a été lancé. Configurez un fournisseur, une clé API, un numéro sortant et une URL de webhook avant de l’activer.');
    }
}
