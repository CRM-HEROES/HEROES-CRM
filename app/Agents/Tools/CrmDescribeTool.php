<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Entities\EntityRegistry;
use App\Agents\Entities\StatsGateway;

/**
 * Lets an agent learn what the CRM holds and how it is organised, from the
 * semantic layer (no database access, no personal data).
 */
class CrmDescribeTool implements AgentToolInterface
{
    public const STATISTICS = 'statistics';

    public function __construct(
        protected EntityRegistry $entities,
        protected StatsGateway $stats,
    ) {
    }

    public function key(): string
    {
        return 'crm_describe';
    }

    public function description(): string
    {
        return "Décrit les données du CRM que tu peux consulter. Sans paramètre : la liste des entités. "
            . "Avec entity : ses champs, ses filtres, ses tris, la façon dont elle est rattachée au projet et ses règles métier. "
            . "Avec entity=statistics : les sources, mesures, dimensions et périodes utilisables avec crm_aggregate, team_performance et compare_periods. "
            . "À utiliser avant de chercher des données, pour savoir ce qui existe et quels filtres sont valides.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'entity' => [
                    'type' => 'string',
                    'description' => "Entité à décrire, ou \"" . self::STATISTICS . "\" (laisser vide pour la liste).",
                    'enum' => array_merge($this->entities->keys(), [self::STATISTICS]),
                ],
            ],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'crm';
    }

    public function risk(): string
    {
        return self::READ;
    }

    public function ability(): ?string
    {
        // Describes the structure of the CRM, reads none of its data
        return null;
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        if (empty($input['entity'])) {
            $list = [];

            foreach ($this->entities->all() as $key => $entity) {
                $list[$key] = ['label' => $entity['label'], 'description' => $entity['description']];
            }

            return ToolResult::ok(
                ['entities' => $list, self::STATISTICS => "Chiffres et classements de l'équipe : décris entity=" . self::STATISTICS . '.'],
                'Liste des entités',
                count($list)
            );
        }

        if ($input['entity'] === self::STATISTICS) {
            return ToolResult::ok($this->stats->catalogue(), 'Catalogue des statistiques', 1);
        }

        $entity = $this->entities->get($input['entity']);

        if (!$entity) {
            return ToolResult::error("Entité inconnue : {$input['entity']}.", ToolResult::NOT_FOUND);
        }

        return ToolResult::ok(
            ['entity' => $input['entity']] + [
                'label' => $entity['label'],
                'description' => $entity['description'],
                'rattachement_au_projet' => $entity['scope'],
                'visibilite' => $entity['visibility'],
                'fields' => $entity['fields'],
                'filters' => $entity['filters'],
                'sorts' => $entity['sorts'],
                'default_sort' => $entity['default_sort'],
                'relations' => $entity['relations'],
                'rules' => $entity['rules'],
            ],
            "Description de {$input['entity']}",
            1
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }
}
