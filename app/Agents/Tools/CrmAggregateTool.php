<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Entities\AgentReadException;
use App\Agents\Entities\StatsGateway;
use App\Utils\Stat\Builder\Sources;

/**
 * Counts and totals computed by the database: how many calls per user, SMS per
 * day, orders per status... on the engine of the dashboards of the CRM.
 */
class CrmAggregateTool implements AgentToolInterface
{
    public function __construct(protected StatsGateway $stats)
    {
    }

    public function key(): string
    {
        return 'crm_aggregate';
    }

    public function description(): string
    {
        return "Calcule un chiffre, ou un chiffre par valeur d'une dimension, sur une période : par exemple le nombre d'appels par utilisateur, de SMS par jour, de commandes par statut, de rendez-vous par calendrier. "
            . "Sources : interactions (appels), sms, orders, prospects, events (rendez-vous), users. "
            . "Sans dimension, renvoie un total. Les chiffres par utilisateur ne concernent que les utilisateurs que tu peux voir. "
            . "Appelle crm_describe avec entity=statistics pour connaître les mesures et dimensions de chaque source. "
            . "Pour comparer deux périodes, utilise compare_periods : ne soustrais pas toi-même.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'source' => ['type' => 'string', 'enum' => array_keys(Sources::all()), 'description' => 'Ce qu\'on compte.'],
                'measure' => ['type' => 'string', 'description' => 'Mesure (par défaut : le nombre). Ex. duration pour la durée des appels.'],
                'agg' => ['type' => 'string', 'enum' => ['count', 'sum', 'avg', 'min', 'max'], 'description' => 'Agrégation (par défaut : celle de la mesure).'],
                'dimension' => ['type' => 'string', 'description' => 'Regrouper par : user, day, week, month, status, source, direction... (voir crm_describe, entity=statistics). Absent : un seul total.'],
                'period' => Schemas::period('Période à analyser (30 derniers jours par défaut).'),
                'user_ids' => Schemas::userIds(),
                'filters' => [
                    'type' => 'array',
                    'description' => "Filtres sur les dimensions de la source (sources d'événements uniquement).",
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'field' => ['type' => 'string', 'description' => 'Une dimension de la source.'],
                            'op' => ['type' => 'string', 'enum' => ['eq', 'neq', 'contains', 'in', 'gt', 'lt']],
                            'value' => ['type' => 'string'],
                        ],
                        'required' => ['field', 'value'],
                        'additionalProperties' => false,
                    ],
                    'maxItems' => 5,
                ],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 120, 'description' => 'Nombre de lignes au plus (50 par défaut).'],
            ],
            'required' => ['source'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'stat';
    }

    public function risk(): string
    {
        return self::READ;
    }

    public function ability(): ?string
    {
        // The figures are limited to the users the owner may see: see StatsGateway
        return null;
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        try {
            $result = $this->stats->aggregate($context, $input);
        } catch (AgentReadException $e) {
            return ToolResult::error($e->getMessage(), $e->errorCode);
        }

        return ToolResult::ok($result, "{$result['source']} : " . count($result['rows']) . ' ligne(s)', count($result['rows']));
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }
}
