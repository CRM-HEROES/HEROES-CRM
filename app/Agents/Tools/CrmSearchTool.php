<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Entities\AgentReadException;
use App\Agents\Entities\EntityRegistry;
use App\Agents\Entities\ReadGateway;

/**
 * Lists the records of the CRM (prospects, users, calls, SMS, appointments,
 * orders) with filters, a choice of fields, a sort and pages.
 */
class CrmSearchTool implements AgentToolInterface
{
    public function __construct(
        protected EntityRegistry $entities,
        protected ReadGateway $gateway,
    ) {
    }

    public function key(): string
    {
        return 'crm_search';
    }

    public function description(): string
    {
        return "Liste des enregistrements du CRM : prospect, user, call, sms, appointment, order. "
            . "Donne l'entité, des filtres (voir crm_describe pour ceux de chaque entité), les champs voulus, un tri, une limite (50 au plus) et un décalage pour la page suivante. "
            . "Renvoie le nombre total de résultats et les lignes demandées : n'en demande pas plus qu'il n'en faut. "
            . "Les données personnelles (e-mail, téléphone) ne sont renvoyées que si tu les demandes dans fields. "
            . "Pour des chiffres ou des classements, utilise plutôt crm_aggregate ou team_performance.";
    }

    public function inputSchema(): array
    {
        $filters = [];

        foreach ($this->entities->all() as $entity) {
            foreach ($entity['filters'] as $name => $definition) {
                $filters[$name] ??= match ($definition['type']) {
                    'integer' => ['type' => 'integer', 'description' => $definition['label']],
                    'boolean' => ['type' => 'boolean', 'description' => $definition['label']],
                    default => ['type' => 'string', 'description' => $definition['label']],
                };
            }
        }

        return [
            'type' => 'object',
            'properties' => [
                'entity' => ['type' => 'string', 'enum' => $this->entities->keys(), 'description' => "Ce qu'on liste."],
                'filters' => [
                    'type' => 'object',
                    'description' => "Filtres valides pour l'entité choisie (crm_describe les donne). Un filtre qui n'existe pas pour l'entité est refusé.",
                    'properties' => $filters,
                    'additionalProperties' => false,
                ],
                'fields' => [
                    'type' => 'array',
                    'description' => "Champs à renvoyer. Par défaut : les champs principaux, sans données personnelles.",
                    'items' => ['type' => 'string'],
                    'maxItems' => 20,
                ],
                'sort' => [
                    'type' => 'object',
                    'properties' => [
                        'field' => ['type' => 'string'],
                        'direction' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                    ],
                    'additionalProperties' => false,
                ],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50, 'description' => 'Nombre de lignes (20 par défaut).'],
                'offset' => ['type' => 'integer', 'minimum' => 0, 'description' => 'Pour la page suivante : next_offset de la réponse précédente.'],
            ],
            'required' => ['entity'],
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
        // Each entity checks its own permission, and the visibility rules of
        // the CRM apply: see ReadGateway
        return null;
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        try {
            $result = $this->gateway->search($context, $input['entity'], array_diff_key($input, ['entity' => true]));
        } catch (AgentReadException $e) {
            return ToolResult::error($e->getMessage(), $e->errorCode);
        }

        return ToolResult::ok(
            $result,
            "{$result['returned']} {$input['entity']} sur {$result['total']}",
            $result['returned']
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }
}
