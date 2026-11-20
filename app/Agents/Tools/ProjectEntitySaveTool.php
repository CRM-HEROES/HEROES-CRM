<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Writes\InternalApi;

/**
 * Creates or changes a piece of the configuration of the project: a ready-made text (SMS, e-mail, appointment), a category of
 * filters and its filters, a group, a pipeline, a folder, a custom field. Through the routes of the CRM, with the right the
 * CRM asks for each kind (the tool checks it too, because some routes do not).
 *
 * It never deletes (project_entity_delete) and never touches what carries users, rights or credentials.
 */
class ProjectEntitySaveTool implements AgentToolInterface, ChecksBeforeApproval
{
    public function __construct(protected InternalApi $api, protected ProjectEntities $entities)
    {
    }

    public function key(): string
    {
        return 'project_entity_save';
    }

    public function description(): string
    {
        return "Crée (sans id) ou modifie (avec id) un élément de configuration du projet. entity : sms_template, message_template (e-mail), event_description_template, category (catégorie de filtres), "
            . "label (filtre ; parent_id = la catégorie), group, pipeline, folder (dossier de prospect), field (champ personnalisé de prospect), document (modèle de document PDF : nom, description, destination, dossier ; ses pages et champs se composent dans l'éditeur), questionnaire (son nom ; sa structure : questionnaire_build), order_status / order_action / order_step (états, actions et étapes de devis), calendar (type de rendez-vous). Les produits et leurs prix ne se règlent pas ici. "
            . "values : les champs de ce type (name, body, subject, description, color et bgcolor « #RRGGBB », unique, private, required, searchable, default_value, et pour un modèle de document for = prospect, order ou invoice et folder_id). "
            . "Pour créer un label : name, color et bgcolor. Les ids se lisent avec project_config ou message_templates. Ne supprime rien (project_entity_delete) et ne touche ni aux rôles, ni aux canaux, ni aux produits.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'entity' => ['type' => 'string', 'enum' => ProjectEntities::kinds()],
                'id' => ['type' => 'integer', 'description' => 'Sans id : création.'],
                'parent_id' => ['type' => 'integer', 'description' => 'entity=label : la catégorie.'],
                'values' => ['type' => 'object', 'properties' => ProjectEntities::valueSchema(), 'additionalProperties' => false],
            ],
            'required' => ['entity', 'values'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'configuration';
    }

    public function risk(): string
    {
        return self::WRITE;
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

        [$method, $path, $body, $kind] = $prepared;
        $response = $this->api->call($context, $method, $path, $body);

        if (!$response->ok()) {
            return $response->toError();
        }

        $creating = $method === 'POST';

        return ToolResult::ok(
            ['done' => true, 'entity' => $input['entity'], 'id' => $creating ? ($response->json['id'] ?? null) : (int) $input['id'], 'created' => $creating],
            ($creating ? 'Créé : ' : 'Modifié : ') . ProjectEntities::KINDS[$input['entity']]['label'],
            1
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        $kind = ProjectEntities::KINDS[$input['entity'] ?? ''] ?? null;

        return [
            'action' => ((int) ($input['id'] ?? 0) > 0 ? 'Modifier' : 'Créer') . ' : ' . ($kind['label'] ?? '?'),
            'valeurs' => array_map(fn ($v) => is_string($v) ? mb_substr($v, 0, 200) : $v, (array) ($input['values'] ?? [])),
            'id' => $input['id'] ?? null,
        ];
    }

    /**
     * What will be called, checked beforehand: [method, path, body, kind], or the refusal.
     *
     * @return ToolResult|array{0: string, 1: string, 2: array, 3: array}
     */
    protected function prepare(AgentExecutionContext $context, array $input): ToolResult|array
    {
        $kind = ProjectEntities::KINDS[$input['entity']];
        $id = (int) ($input['id'] ?? 0);
        $creating = $id === 0;
        $action = $creating ? 'create' : 'update';

        if ($right = $kind['rights'][$action]) {
            if (!$context->owner->can($right, $context->project)) {
                return ToolResult::error("L'utilisateur propriétaire de l'agent n'a pas le droit de " . ($creating ? 'créer' : 'modifier') . ' un(e) ' . $kind['label'] . ' dans ce projet.', ToolResult::PERMISSION_DENIED);
            }
        }

        $values = $this->entities->values($input['entity'], $input, $creating);
        if (is_string($values)) {
            return ToolResult::error($values, ToolResult::VALIDATION_FAILED);
        }

        // A template of document is kept in a folder of this project
        if (!empty($values['folder_id']) && !\App\Models\Folder::query()->where('project_id', $context->project->id)->whereKey($values['folder_id'])->exists()) {
            return ToolResult::error("folder_id : dossier inconnu dans ce projet (project_config donne les identifiants).", ToolResult::NOT_FOUND);
        }

        $parent = isset($kind['parent']) ? (int) ($input['parent_id'] ?? 0) : null;
        if (isset($kind['parent'])) {
            if ($parent <= 0 || !\App\Models\Category::query()->where('project_id', $context->project->id)->whereKey($parent)->exists()) {
                return ToolResult::error("parent_id : catégorie inconnue dans ce projet (project_config donne les identifiants).", ToolResult::NOT_FOUND);
            }
        }

        $route = str_replace('{parent}', (string) $parent, $kind['route']);

        if ($creating) {
            return ['POST', $route, $values + ($kind['fixed'] ?? []), $kind];
        }

        if (!$this->entities->find($context, $input['entity'], $id, $parent)) {
            return ToolResult::error("{$kind['label']} $id introuvable dans ce projet" . ($input['entity'] === 'field' ? ' (seuls les champs personnalisés se modifient).' : '.'), ToolResult::NOT_FOUND);
        }

        return ['PUT', "$route/$id", $values, $kind];
    }
}
