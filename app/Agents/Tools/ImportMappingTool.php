<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Models\Category;
use App\Models\Field;
use App\Models\Group;
use App\Models\Import;
use App\Models\Label;
use App\Models\Thread;

/**
 * How the columns of an import are linked to the prospect: for each column, what it fills (a field, a custom field,
 * a filter category, a channel, an appointment…), what is not linked yet, the fields that detect duplicates, and the
 * labels and groups every imported prospect receives. With view=targets, what a column can be linked to in this project.
 *
 * Only the titles of the columns are given: no row of the file is ever read.
 */
class ImportMappingTool implements AgentToolInterface
{
    /** What a column can fill besides a field, a filter category or a channel: type => meaning */
    public const SPECIAL = [
        'events' => 'Rendez-vous',
        'orders' => 'Devis',
        'sms' => 'SMS',
        'interactions' => 'Appel',
        'links' => 'Lien externe',
        'users' => 'Utilisateurs affectés',
    ];

    protected const MAX_COLUMNS = 200;

    public function key(): string
    {
        return 'import_mapping';
    }

    public function description(): string
    {
        return "Lie les colonnes d'un import de prospects aux champs du CRM. view=columns (défaut) : pour chaque colonne (numéro, titre) ce qu'elle remplit, celles qui ne sont pas reliées, "
            . "les champs de détection des doublons, les labels et groupes appliqués à tous les prospects importés. view=targets : ce à quoi une colonne peut être reliée dans ce projet "
            . "(champs, champs personnalisés, catégories de filtres, canaux, et les types spéciaux). Ne lit jamais les lignes du fichier. Pour modifier : import_configure.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'import_id' => ['type' => 'integer'],
                'view' => ['type' => 'string', 'enum' => ['columns', 'targets']],
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
        return self::READ;
    }

    public function ability(): ?string
    {
        return 'prospectImport';
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $import = $this->import($context, (int) $input['import_id']);

        if (!$import) {
            return ToolResult::error("Import {$input['import_id']} introuvable dans ce projet, ou non accessible pour cet utilisateur.", ToolResult::NOT_FOUND);
        }

        return ($input['view'] ?? 'columns') === 'targets'
            ? $this->targets($context, $import)
            : $this->columns($context, $import);
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }

    protected function columns(AgentExecutionContext $context, Import $import): ToolResult
    {
        $headers = array_slice(array_values((array) $import->headers), 0, self::MAX_COLUMNS);
        $mapping = array_values((array) $import->mapping);
        $projectId = $context->project->id;

        $fields = Field::query()->where('project_id', $projectId)->where('for', 'prospect')->get(['id', 'slug', 'name', 'meta'])->keyBy(fn ($f) => ($f->meta ? 'meta->' : '') . $f->slug);
        $categories = Category::query()->where('project_id', $projectId)->get(['id', 'name'])->keyBy('id');
        $threads = Thread::query()->where('project_id', $projectId)->get(['id', 'name'])->keyBy('id');

        $columns = [];
        foreach ($headers as $index => $header) {
            if ($header === null || trim((string) $header) === '') {
                continue;
            }
            $columns[] = [
                'column' => $index,
                'header' => mb_substr(strip_tags((string) $header), 0, 80),
                'target' => $this->describe($mapping[$index] ?? null, $fields, $categories, $threads),
            ];
        }

        $duplicateFields = Field::query()->where('project_id', $projectId)->whereIn('id', array_map('intval', (array) $import->duplicates_fields))->get(['slug', 'name', 'meta']);

        return ToolResult::ok(
            [
                'import_id' => $import->id,
                'is_processing' => (bool) $import->is_processing,
                'columns' => $columns,
                'not_mapped' => array_values(array_map(fn ($c) => $c['column'], array_filter($columns, fn ($c) => $c['target'] === null))),
                'duplicates_detected_on' => $duplicateFields->map(fn ($f) => ['slug' => ($f->meta ? 'meta->' : '') . $f->slug, 'name' => $f->name])->all(),
                'labels_applied' => Label::query()->whereIn('id', array_map('intval', (array) $import->labels))->get(['id', 'name'])->map(fn ($l) => ['id' => $l->id, 'name' => $l->name])->all(),
                'groups_applied' => Group::query()->where('project_id', $projectId)->whereIn('id', array_map('intval', (array) $import->groups))->get(['id', 'name'])->map(fn ($g) => ['id' => $g->id, 'name' => $g->name])->all(),
            ],
            "Mapping de l'import {$import->id}",
            count($columns)
        );
    }

    protected function targets(AgentExecutionContext $context, Import $import): ToolResult
    {
        $projectId = $context->project->id;

        return ToolResult::ok(
            [
                'import_id' => $import->id,
                'fields' => Field::query()->where('project_id', $projectId)->where('for', 'prospect')->orderBy('order')->limit(300)->get(['slug', 'name', 'meta'])
                    ->map(fn ($f) => ['type' => $f->meta ? 'meta' : 'field', 'slug' => $f->slug, 'name' => $f->name])->all(),
                'categories' => Category::query()->where('project_id', $projectId)->where('for', 'prospect')->orderBy('name')->limit(100)->get(['id', 'name'])
                    ->map(fn ($c) => ['type' => 'category', 'id' => $c->id, 'name' => $c->name])->all(),
                'threads' => Thread::query()->where('project_id', $projectId)->orderBy('name')->limit(100)->get(['id', 'name'])
                    ->map(fn ($t) => ['type' => 'thread', 'id' => $t->id, 'name' => $t->name])->all(),
                'special' => collect(self::SPECIAL)->map(fn ($label, $type) => ['type' => $type, 'meaning' => $label])->values()->all(),
                'note' => "Un champ créé à la volée (« + Champ », « + Filtre ») se fait dans l'interface.",
            ],
            "Cibles possibles pour l'import {$import->id}",
            1
        );
    }

    /**
     * What a column is linked to, in words an agent can say back.
     */
    protected function describe(mixed $value, $fields, $categories, $threads): ?array
    {
        if (!is_string($value) || $value === '') {
            return null;
        }
        if (isset(self::SPECIAL[$value])) {
            return ['type' => $value, 'name' => self::SPECIAL[$value]];
        }
        if (str_starts_with($value, 'category->')) {
            $id = (int) substr($value, 10);

            return ['type' => 'category', 'id' => $id, 'name' => $categories[$id]->name ?? null];
        }
        if (str_starts_with($value, 'thread->')) {
            $id = (int) substr($value, 8);

            return ['type' => 'thread', 'id' => $id, 'name' => $threads[$id]->name ?? null];
        }

        $field = $fields[$value] ?? null;

        return [
            'type' => str_starts_with($value, 'meta->') ? 'meta' : 'field',
            'slug' => str_starts_with($value, 'meta->') ? substr($value, 6) : $value,
            'name' => $field?->name,
        ];
    }

    protected function import(AgentExecutionContext $context, int $id): ?Import
    {
        return $id > 0 ? Import::query()->where('project_id', $context->project->id)->find($id) : null;
    }
}
