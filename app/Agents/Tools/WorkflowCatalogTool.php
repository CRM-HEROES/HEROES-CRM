<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Workflows\Providers\ProviderRegistry;
use App\Workflows\WorkflowCatalog;

/**
 * Everything needed to build a workflow: the format of a graph, the triggers,
 * the actions and their fields, the conditions, the variables, and the
 * operators (SMS, e-mail, call) configured in the project. It is the catalogue
 * the workflow builder of the interface uses: the CRM is the source of truth,
 * the agent reads it instead of guessing.
 */
class WorkflowCatalogTool implements AgentToolInterface
{
    public function __construct(protected WorkflowCatalog $catalog, protected ProviderRegistry $providers)
    {
    }

    public function key(): string
    {
        return 'workflow_catalog';
    }

    public function description(): string
    {
        return "Décrit comment construire un workflow : format du graphe, déclencheurs, actions (SMS, e-mail, appel...), conditions, variables {{...}} et opérateurs du projet. À lire AVANT workflow_save_draft.";
    }

    public function inputSchema(): array
    {
        return ['type' => 'object', 'properties' => new \stdClass(), 'additionalProperties' => false];
    }

    public function module(): string
    {
        return 'workflow';
    }

    public function risk(): string
    {
        return self::READ;
    }

    public function ability(): ?string
    {
        return 'projectWorkflowView';
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $operators = array_map(
            fn ($p) => ['ref' => $p['ref'], 'label' => $p['label'], 'capabilities' => $p['capabilities']],
            $this->providers->forProject($context->project->id)
        );

        return ToolResult::ok([
            'graph_format' => [
                'summary' => 'Un graphe = des étapes (nodes) reliées (edges). Il commence par UN nœud trigger et chaque chemin finit par un nœud end. Pas de boucle.',
                'node' => '{ "id": "n1", "type": "trigger|condition|action|wait|wait_event|end", "name": "libellé facultatif", "config": {...} }',
                'edge' => '{ "from": "n1", "to": "n2", "branch": "yes|no" }  (branch seulement en sortie d\'une condition ou d\'un wait_event)',
                'trigger' => 'config: { "trigger": "<type de la liste triggers>", ...champs du déclencheur }. Le nœud trigger de départ s\'appelle "trigger".',
                'action' => 'config: { "action": "<type de la liste actions>", "provider": "<ref d\'un opérateur>", ...champs de l\'action }',
                'wait' => 'config: { "minutes": 0, "hours": 0, "days": 2 } ou { "until_date": "AAAA-MM-JJ" } ou { "until_time": "HH:MM", "days": [1..7] }',
                'wait_event' => 'config: { "event": "<un des events>", "days"/"hours"/"minutes": délai maximal } ; branche yes si l\'événement arrive, no si le délai passe',
                'condition' => 'config: { "condition": <voir conditions> } ; branches yes / no',
                'variables' => 'Dans les messages : {{prospect.firstname}}, {{user.fullname}}, {{project.name}}... uniquement celles de la liste variables.',
                'example' => [
                    'nodes' => [
                        ['id' => 'trigger', 'type' => 'trigger', 'config' => ['trigger' => 'prospect.created']],
                        ['id' => 'w1', 'type' => 'wait', 'config' => ['days' => 1]],
                        ['id' => 's1', 'type' => 'action', 'config' => ['action' => 'send_sms', 'provider' => '<ref>', 'message' => 'Bonjour {{prospect.firstname}}']],
                        ['id' => 'end1', 'type' => 'end', 'config' => []],
                    ],
                    'edges' => [['from' => 'trigger', 'to' => 'w1'], ['from' => 'w1', 'to' => 's1'], ['from' => 's1', 'to' => 'end1']],
                ],
            ],
            'catalog' => $this->catalog->build(),
            'operators' => $operators,
            'lifecycle' => "workflow_save_draft enregistre un brouillon (il renvoie les problèmes à corriger) ; workflow_publish le fige en version ; workflow_set_status l'active (validé par l'utilisateur).",
        ], 'Catalogue des workflows', 1);
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }
}
