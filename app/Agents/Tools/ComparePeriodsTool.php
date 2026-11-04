<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Entities\AgentReadException;
use App\Agents\Entities\StatsGateway;

/**
 * Compares the team over two periods; the differences, percentages and trends
 * are computed by the server, not by the model.
 */
class ComparePeriodsTool implements AgentToolInterface
{
    public function __construct(protected StatsGateway $stats)
    {
    }

    public function key(): string
    {
        return 'compare_periods';
    }

    public function description(): string
    {
        return "Compare l'activité de chaque personne de l'équipe entre deux périodes (par exemple cette semaine et la semaine dernière) : "
            . "pour chaque personne et chaque indicateur, valeur actuelle, valeur précédente, écart, écart en pourcentage et tendance (improved, declined, stable), "
            . "plus un résumé (qui s'est le plus amélioré, qui a le plus baissé). Les calculs sont faits par le serveur : cite-les tels quels. "
            . "period est la période récente, compare_to la période de référence. Ne concerne que les utilisateurs que tu peux voir.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'period' => Schemas::period('La période récente (ex. this_week).'),
                'compare_to' => Schemas::period('La période de référence (ex. last_week).'),
                'metrics' => [
                    'type' => 'array',
                    'description' => 'Indicateurs à comparer (tous par défaut).',
                    'items' => ['type' => 'string', 'enum' => StatsGateway::COMPARABLE],
                    'maxItems' => 5,
                ],
                'user_ids' => Schemas::userIds(),
            ],
            'required' => ['period', 'compare_to'],
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
        return null;
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        try {
            $result = $this->stats->compare($context, $input['period'], $input['compare_to'], $input['metrics'] ?? null, $input['user_ids'] ?? null);
        } catch (AgentReadException $e) {
            return ToolResult::error($e->getMessage(), $e->errorCode);
        }

        return ToolResult::ok($result, count($result['people']) . ' personne(s) comparée(s)', count($result['people']));
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }
}
