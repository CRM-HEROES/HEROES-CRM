<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Writes\InternalApi;

/**
 * Deletes a piece of the configuration of the project (see ProjectEntitySaveTool for the kinds). Always decided by a human.
 * What was deleted goes to the trash of the CRM when the CRM keeps it there; a pipeline, a group or a text is gone.
 */
class ProjectEntityDeleteTool implements AgentToolInterface, ChecksBeforeApproval
{
    public function __construct(protected InternalApi $api, protected ProjectEntities $entities)
    {
    }

    public function key(): string
    {
        return 'project_entity_delete';
    }

    public function description(): string
    {
        return "Supprime un élément de configuration du projet (mêmes types que project_entity_save). Toujours validé par l'utilisateur : annonce ce qui disparaît. "
            . "Supprimer une catégorie ou un champ touche toutes les fiches qui l'utilisent ; vérifie ce qu'il contient avant de le proposer.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'entity' => ['type' => 'string', 'enum' => ProjectEntities::kinds()],
                'id' => ['type' => 'integer'],
                'parent_id' => ['type' => 'integer', 'description' => 'entity=label : la catégorie du filtre.'],
            ],
            'required' => ['entity', 'id'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'configuration';
    }

    public function risk(): string
    {
        return self::DESTRUCTIVE;
    }

    public function ability(): ?string
    {
        return null;
    }

    public function precheck(AgentExecutionContext $context, array $input): ?ToolResult
    {
        $prepared = $this->prepare($context, $input);

        return $prepared instanceof ToolResult ? $prepared : null;
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $prepared = $this->prepare($context, $input);

        if ($prepared instanceof ToolResult) {
            return $prepared;
        }

        $response = $this->api->call($context, 'DELETE', $prepared);

        if (!$response->ok()) {
            return $response->toError();
        }

        return ToolResult::ok(['done' => true, 'entity' => $input['entity'], 'id' => (int) $input['id']], 'Supprimé : ' . ProjectEntities::KINDS[$input['entity']]['label'], 1);
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        $kind = ProjectEntities::KINDS[$input['entity'] ?? ''] ?? null;
        $item = $kind ? $this->entities->find($context, $input['entity'], (int) ($input['id'] ?? 0), isset($input['parent_id']) ? (int) $input['parent_id'] : null) : null;

        return [
            'action' => 'Supprimer : ' . ($kind['label'] ?? '?'),
            'nom' => $item ? mb_substr(strip_tags((string) ($item->name ?? '')), 0, 120) : null,
            'id' => $input['id'] ?? null,
        ];
    }

    /**
     * The route to call, checked beforehand, or the refusal.
     */
    protected function prepare(AgentExecutionContext $context, array $input): ToolResult|string
    {
        $kind = ProjectEntities::KINDS[$input['entity']];
        $id = (int) $input['id'];

        if (($right = $kind['rights']['delete']) && !$context->owner->can($right, $context->project)) {
            return ToolResult::error("L'utilisateur propriétaire de l'agent n'a pas le droit de supprimer un(e) {$kind['label']} dans ce projet.", ToolResult::PERMISSION_DENIED);
        }

        $parent = isset($kind['parent']) ? (int) ($input['parent_id'] ?? 0) : null;
        if (isset($kind['parent']) && $parent <= 0) {
            return ToolResult::error('parent_id est obligatoire pour supprimer un filtre (la catégorie).', ToolResult::VALIDATION_FAILED);
        }

        if (!$this->entities->find($context, $input['entity'], $id, $parent)) {
            return ToolResult::error("{$kind['label']} $id introuvable dans ce projet" . ($input['entity'] === 'field' ? ' (seuls les champs personnalisés se suppriment).' : '.'), ToolResult::NOT_FOUND);
        }

        return str_replace('{parent}', (string) $parent, $kind['route']) . "/$id";
    }
}
