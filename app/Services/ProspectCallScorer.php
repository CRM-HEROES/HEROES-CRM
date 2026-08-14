<?php

namespace App\Services;

/**
 * Converts the structured analysis into one consistent score. Keeping this
 * deterministic makes the value auditable even when the LLM wording varies.
 */
class ProspectCallScorer
{
    public function score(array $analysis): array
    {
        $probability = data_get($analysis, 'conversion_probability');
        $probability = is_numeric($probability) ? (int) $probability : null;

        if ($probability === null) {
            $interest = strtolower((string) data_get($analysis, 'interest_level', ''));
            $urgency = strtolower((string) data_get($analysis, 'urgency', ''));
            $probability = match ($interest) {
                'high', 'élevé', 'eleve' => 75,
                'medium', 'moyen' => 50,
                'low', 'faible' => 25,
                default => 0,
            };
            if (in_array($urgency, ['immediate', 'urgent', 'immédiat', 'urgente'], true)) {
                $probability += 10;
            }
        }

        $score = max(0, min(100, $probability));
        $qualification = $score >= 70 ? 'hot' : ($score >= 40 ? 'warm' : 'cold');

        return compact('score', 'qualification') + ['conversion_probability' => $score];
    }
}
