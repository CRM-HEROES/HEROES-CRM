<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Entities\AgentReadException;
use App\Agents\Entities\StatsGateway;

/**
 * The figures of each person of the team over a period.
 */
class TeamPerformanceTool implements AgentToolInterface
{
    public function __construct(protected StatsGateway $stats)
    {
    }

    public function key(): string
    {
        return 'team_performance';
    }

    public function description(): string
    {
        $metrics = implode('; ', array_map(fn ($key, $metric) => "$key = {$metric['label']} ({$metric['unit']})", array_keys(StatsGateway::TEAM_METRICS), StatsGateway::TEAM_METRICS));

        return "Un tableau de l'activité de chaque personne de l'équipe sur une période : $metrics. "
            . "À utiliser pour classer, comparer des personnes entre elles ou repérer les meilleurs résultats. "
            . "Ne concerne que les utilisateurs que tu peux voir. Pour comparer deux périodes, utilise compare_periods.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'period' => Schemas::period('Période à analyser (30 derniers jours par défaut).'),
                'user_ids' => Schemas::userIds(),
            ],
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
            $result = $this->stats->team($context, $input['period'] ?? null, $input['user_ids'] ?? null);
        } catch (AgentReadException $e) {
            return ToolResult::error($e->getMessage(), $e->errorCode);
        }

        return ToolResult::ok(
            $result + ['units' => array_map(fn ($metric) => $metric['unit'], StatsGateway::TEAM_METRICS)],
            count($result['rows']) . ' personne(s)',
            count($result['rows'])
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }
}
