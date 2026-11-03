<?php

namespace App\Agents\Tools;

use App\Utils\Stat\Builder\DashboardEngine;

/**
 * Pieces of JSON Schema shared by several tools.
 */
class Schemas
{
    /**
     * A period: a preset ("last_7_days", "this_month"...) or a custom range.
     */
    public static function period(string $description): array
    {
        return [
            'type' => 'object',
            'description' => $description,
            'properties' => [
                'preset' => [
                    'type' => 'string',
                    'enum' => array_keys(DashboardEngine::PERIODS),
                    'description' => "Période prédéfinie. Utilise \"custom\" avec from et to pour des dates précises.",
                ],
                'from' => ['type' => 'string', 'description' => 'Début (AAAA-MM-JJ), avec preset "custom".'],
                'to' => ['type' => 'string', 'description' => 'Fin (AAAA-MM-JJ), avec preset "custom".'],
            ],
            'required' => ['preset'],
            'additionalProperties' => false,
        ];
    }

    public static function userIds(): array
    {
        return [
            'type' => 'array',
            'description' => "Limiter à ces utilisateurs (identifiants, voir l'entité user). Vide : toute l'équipe visible.",
            'items' => ['type' => 'integer'],
            'maxItems' => 50,
        ];
    }
}
