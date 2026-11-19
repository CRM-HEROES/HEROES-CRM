<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Writes\InternalApi;
use App\Models\Category;
use App\Models\Field;
use App\Models\Group;
use App\Models\Import;
use App\Models\Label;
use App\Models\Thread;

/**
 * Sets up an import that already exists, as the tabs "Mapping" and "Importer" of the interface do: which column fills
 * what, which fields detect duplicates, which labels and groups every imported prospect receives.
 *
 * It only changes the configuration of the import, never a prospect: the import itself is launched by import_run.
 * Everything is checked before the first call, so a wrong request changes nothing. The routes of the CRM do not check
 * a right themselves: the tool declares the one of the import screen.
 */
class ImportConfigureTool implements AgentToolInterface
{
    public const MAX_COLUMNS = 60;
    public const MAX_RELATIONS = 20;

    public const TYPES = ['field', 'meta', 'category', 'thread', 'events', 'orders', 'sms', 'interactions', 'links', 'users', 'none'];

    /** type of a special target => segment of the route of the CRM */
    protected const SPECIAL_ROUTES = [
        'events' => 'event',
        'orders' => 'order',
        'sms' => 'sms',
        'interactions' => 'interaction',
        'links' => 'link',
        'users' => 'user',
    ];

    public function __construct(protected InternalApi $api)
    {
    }

    public function key(): string
    {
        return 'import_configure';
    }

    public function description(): string
    {
        return "Configure un import de prospects déjà créé et pas encore lancé : relier des colonnes (mapping : column = numéro de colonne de import_mapping, target = {type, slug ou id}), "
            . "définir les champs de détection des doublons (duplicates_fields : slugs de champs), ajouter ou retirer des labels (add_labels / remove_labels) et des groupes (add_groups / remove_groups) appliqués à tous les prospects importés. "
            . "target.type : field ou meta (avec slug), category ou thread (avec id), events, orders, sms, interactions, links, users, ou none pour délier. "
            . "Lis d'abord import_mapping (view=targets pour les cibles). Un import en cours ne se modifie pas. Cet outil ne lance pas l'import (import_run).";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'import_id' => ['type' => 'integer'],
                'mapping' => [
                    'type' => 'array',
                    'maxItems' => self::MAX_COLUMNS,
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'column' => ['type' => 'integer', 'minimum' => 0],
                            'target' => [
                                'type' => 'object',
                                'properties' => [
                                    'type' => ['type' => 'string', 'enum' => self::TYPES],
                                    'slug' => ['type' => 'string', 'maxLength' => 100],
                                    'id' => ['type' => 'integer'],
                                ],
                                'required' => ['type'],
                                'additionalProperties' => false,
                            ],
                        ],
                        'required' => ['column', 'target'],
                        'additionalProperties' => false,
                    ],
                ],
                'duplicates_fields' => ['type' => 'array', 'items' => ['type' => 'string', 'maxLength' => 100], 'maxItems' => 10, 'description' => 'Slugs des champs (« meta->slug » pour un champ personnalisé). Liste vide : plus de détection.'],
                'add_labels' => ['type' => 'array', 'items' => ['type' => 'integer'], 'maxItems' => self::MAX_RELATIONS],
                'remove_labels' => ['type' => 'array', 'items' => ['type' => 'integer'], 'maxItems' => self::MAX_RELATIONS],
                'add_groups' => ['type' => 'array', 'items' => ['type' => 'integer'], 'maxItems' => self::MAX_RELATIONS],
                'remove_groups' => ['type' => 'array', 'items' => ['type' => 'integer'], 'maxItems' => self::MAX_RELATIONS],
            ],
            'required' => ['import_id'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'import';
    }

    public function risk(): string
    {
        return self::WRITE;
    }

    public function ability(): ?string
    {
        return 'prospectImport';
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $import = $input['import_id'] > 0
            ? Import::query()->where('project_id', $context->project->id)->find((int) $input['import_id'])
            : null;

        if (!$import) {
            return ToolResult::error("Import {$input['import_id']} introuvable dans ce projet, ou non accessible pour cet utilisateur.", ToolResult::NOT_FOUND);
        }
        if ($import->is_processing) {
            return ToolResult::error('Cet import est en cours : sa configuration ne se modifie pas.', ToolResult::INVALID_INPUT);
        }
        if (!in_array($import->source, ['file', 'google_sheets'], true)) {
            return ToolResult::error('Seuls les imports de fichier ou de Google Sheets se configurent ici.', ToolResult::INVALID_INPUT);
        }

        $plan = $this->plan($context, $import, $input);
        if ($plan instanceof ToolResult) {
            return $plan;
        }
        if (!$plan) {
            return ToolResult::error('Rien à configurer : donne un mapping, des champs de doublons, des labels ou des groupes.', ToolResult::VALIDATION_FAILED);
        }

        $applied = 0;
        $failed = [];
        foreach ($plan as [$what, $method, $path, $body]) {
            $response = $this->api->call($context, $method, "import/{$import->id}" . $path, $body);

            if ($response->ok()) {
                $applied++;
            } else {
                $failed[] = ['what' => $what, 'error' => $response->toError()->data['error'] ?? 'Refusé par le CRM.'];
            }
        }

        if (!$applied && $failed) {
            return ToolResult::error('Aucune modification appliquée : ' . $failed[0]['error'], ToolResult::VALIDATION_FAILED);
        }

        return ToolResult::ok(
            ['import_id' => $import->id, 'applied' => $applied, 'failed' => $failed, 'next' => 'Vérifie avec import_mapping, puis lance avec import_run.'],
            "Import {$import->id} : $applied modification(s) appliquée(s)" . ($failed ? ', ' . count($failed) . ' en échec' : ''),
            $applied
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [
            'action' => "Configurer l'import (colonnes, doublons, labels, groupes)",
            'import_id' => $input['import_id'] ?? null,
            'colonnes' => count((array) ($input['mapping'] ?? [])),
            'doublons' => isset($input['duplicates_fields']) ? count($input['duplicates_fields']) : null,
            'labels' => ['ajout' => count((array) ($input['add_labels'] ?? [])), 'retrait' => count((array) ($input['remove_labels'] ?? []))],
            'groupes' => ['ajout' => count((array) ($input['add_groups'] ?? [])), 'retrait' => count((array) ($input['remove_groups'] ?? []))],
        ];
    }

    /**
     * Every call to make, checked beforehand: [what, method, path (after import/{id}), body]. Or the error that stops everything.
     *
     * @return array<int, array>|ToolResult
     */
    protected function plan(AgentExecutionContext $context, Import $import, array $input): array|ToolResult
    {
        $projectId = $context->project->id;
        $plan = [];
        $columns = count((array) $import->headers);
        $seen = [];

        foreach ((array) ($input['mapping'] ?? []) as $item) {
            $column = (int) $item['column'];
            $target = $item['target'];
            $type = $target['type'];

            if ($column < 0 || $column >= $columns) {
                return ToolResult::error("La colonne $column n'existe pas : cet import a $columns colonne(s), numérotées à partir de 0.", ToolResult::VALIDATION_FAILED);
            }
            if (isset($seen[$column])) {
                return ToolResult::error("La colonne $column est donnée deux fois.", ToolResult::VALIDATION_FAILED);
            }
            $seen[$column] = true;
            $base = "/mapping/$column";

            if ($type === 'none') {
                $plan[] = ["délier la colonne $column", 'DELETE', $base, []];
            } elseif (isset(self::SPECIAL_ROUTES[$type])) {
                $plan[] = ["colonne $column → $type", 'PUT', $base . '/' . self::SPECIAL_ROUTES[$type], []];
            } elseif ($type === 'field' || $type === 'meta') {
                $slug = (string) ($target['slug'] ?? '');
                $exists = $slug !== '' && Field::query()->where('project_id', $projectId)->where('for', 'prospect')->where('meta', $type === 'meta')->where('slug', $slug)->exists();
                if (!$exists) {
                    return ToolResult::error("Champ « $slug » inconnu (type $type) : voir import_mapping view=targets.", ToolResult::VALIDATION_FAILED);
                }
                $plan[] = ["colonne $column → $type $slug", 'PUT', "$base/$type/" . rawurlencode($slug), []];
            } elseif ($type === 'category' || $type === 'thread') {
                $id = (int) ($target['id'] ?? 0);
                $model = $type === 'category' ? Category::class : Thread::class;
                if (!$id || !$model::query()->where('project_id', $projectId)->whereKey($id)->exists()) {
                    return ToolResult::error("Cible $type $id inconnue dans ce projet : voir import_mapping view=targets.", ToolResult::VALIDATION_FAILED);
                }
                $plan[] = ["colonne $column → $type $id", 'PUT', "$base/$type/$id", []];
            }
        }

        if (array_key_exists('duplicates_fields', $input)) {
            $ids = [];
            foreach ((array) $input['duplicates_fields'] as $slug) {
                $meta = str_starts_with((string) $slug, 'meta->');
                $field = Field::query()->where('project_id', $projectId)->where('for', 'prospect')
                    ->where('meta', $meta)->where('slug', $meta ? substr((string) $slug, 6) : $slug)->first(['id']);
                if (!$field) {
                    return ToolResult::error("Champ de doublons « $slug » inconnu dans ce projet.", ToolResult::VALIDATION_FAILED);
                }
                $ids[] = (int) $field->id;
            }
            $plan[] = ['champs de détection des doublons', 'PUT', '', ['duplicates_fields' => array_values(array_unique($ids))]];
        }

        foreach (['add_labels' => ['PUT', Label::class], 'remove_labels' => ['DELETE', Label::class]] as $key => [$method]) {
            foreach (array_unique(array_map('intval', (array) ($input[$key] ?? []))) as $id) {
                $label = Label::query()->whereKey($id)->whereHas('category', fn ($c) => $c->where('project_id', $projectId))->first(['id']);
                if (!$label) {
                    return ToolResult::error("Label $id inconnu dans ce projet (project_config donne les identifiants).", ToolResult::VALIDATION_FAILED);
                }
                $plan[] = [($method === 'PUT' ? 'ajouter' : 'retirer') . " le label $id", $method, "/label/$id", []];
            }
        }

        foreach (['add_groups' => 'PUT', 'remove_groups' => 'DELETE'] as $key => $method) {
            foreach (array_unique(array_map('intval', (array) ($input[$key] ?? []))) as $id) {
                if (!Group::query()->where('project_id', $projectId)->whereKey($id)->exists()) {
                    return ToolResult::error("Groupe $id inconnu dans ce projet (project_config donne les identifiants).", ToolResult::VALIDATION_FAILED);
                }
                $plan[] = [($method === 'PUT' ? 'ajouter' : 'retirer') . " le groupe $id", $method, "/group/$id", []];
            }
        }

        return $plan;
    }
}
