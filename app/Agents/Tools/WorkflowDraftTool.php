<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Writes\InternalApi;

/**
 * Creates a workflow, or updates the draft of one, through the routes of the
 * workflow builder. A draft does nothing: it is saved even incomplete and the
 * CRM answers with the problems still to fix, which the agent reads and
 * corrects. Publishing and activating are separate, more guarded, tools.
 */
class WorkflowDraftTool implements AgentToolInterface
{
    public function __construct(protected InternalApi $api)
    {
    }

    public function key(): string
    {
        return 'workflow_save_draft';
    }

    public function description(): string
    {
        return "Crée un workflow en brouillon (sans workflow_id) ou met à jour le brouillon d'un workflow existant (avec workflow_id). "
            . "Le graphe suit le format de workflow_catalog. Le brouillon n'exécute rien ; la réponse liste les problèmes restants à corriger (problems), "
            . "à relire et corriger jusqu'à ce qu'il n'y en ait plus avant de publier.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'workflow_id' => ['type' => 'integer', 'description' => 'Workflow existant à modifier (absent : création).'],
                'name' => ['type' => 'string', 'maxLength' => 150],
                'description' => ['type' => 'string', 'maxLength' => 2000],
                'graph' => [
                    'type' => 'object',
                    'description' => 'Graphe complet { nodes: [...], edges: [...] } (format : workflow_catalog).',
                    'properties' => [
                        'nodes' => ['type' => 'array', 'items' => ['type' => 'object'], 'maxItems' => 100],
                        'edges' => ['type' => 'array', 'items' => ['type' => 'object'], 'maxItems' => 300],
                    ],
                ],
            ],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'workflow';
    }

    public function risk(): string
    {
        return self::WRITE;
    }

    public function ability(): ?string
    {
        return 'projectWorkflowUpdate';
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $id = $input['workflow_id'] ?? null;

        if (!$id && empty($input['name'])) {
            return ToolResult::error('Un nouveau workflow a besoin d\'un nom.', ToolResult::INVALID_INPUT);
        }

        if ($id === null) {
            // Creating also needs the ability to add
            if (!auth()->user()->can('projectWorkflowAdd', $context->project)) {
                return ToolResult::error('L\'utilisateur n\'a pas le droit de créer un workflow.', ToolResult::PERMISSION_DENIED);
            }

            $created = $this->api->call($context, 'POST', 'workflow', array_filter(['name' => $input['name'], 'description' => $input['description'] ?? null]));

            if (!$created->ok()) {
                return $created->toError();
            }

            $id = $created->json['id'] ?? null;
        }

        $body = array_diff_key($input, ['workflow_id' => 1]);

        if ($input['workflow_id'] ?? null) {
            // On an existing workflow only what is given changes
            $body = array_filter($body, fn ($v) => $v !== null);
        } else {
            unset($body['name']);
        }

        if (!$body) {
            return ToolResult::ok(['workflow_id' => $id, 'status' => 'draft', 'problems' => []], 'Workflow créé', 1);
        }

        $saved = $this->api->call($context, 'PUT', "workflow/$id", ($body['graph'] ?? null) ? ['draft_graph' => $body['graph']] + array_diff_key($body, ['graph' => 1]) : $body);

        if (!$saved->ok()) {
            return $saved->toError();
        }

        $problems = $saved->json['problems'] ?? [];

        return ToolResult::ok([
            'workflow_id' => $id,
            'status' => $saved->json['status'] ?? 'draft',
            'problems' => array_map(fn ($p) => is_array($p) ? array_filter(['step' => $p['node'] ?? $p['node_id'] ?? null, 'message' => $p['message'] ?? null]) : $p, $problems),
            'ready_to_publish' => $problems === [],
        ], $problems ? count($problems) . ' problème(s) restant(s)' : 'Brouillon valide', 1);
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }
}
