<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Models\Import;

/**
 * The imports of prospects of the project (file, Google Sheets): which exist, where each one stands, and how far its
 * mapping is done. The headers of the columns are given (they are titles, not data); no row of the file ever is.
 */
class ImportStatusTool implements AgentToolInterface
{
    public const STATES = ['to_launch', 'processing', 'done'];

    public function key(): string
    {
        return 'import_status';
    }

    public function description(): string
    {
        return "Imports de prospects du projet (fichier ou Google Sheets). view=list : les imports avec leur état (to_launch = pas encore lancé, processing = en cours, done = terminé), "
            . "le nombre de lignes et la synchronisation. view=detail : un import avec les colonnes déjà reliées à un champ, celles qui ne le sont pas, les champs de détection des doublons et le nombre de prospects créés. "
            . "Ne donne jamais le contenu des lignes du fichier.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'view' => ['type' => 'string', 'enum' => ['list', 'detail']],
                'import_id' => ['type' => 'integer', 'description' => 'view=detail : l\'import.'],
                'state' => ['type' => 'string', 'enum' => self::STATES, 'description' => 'view=list : limiter à un état.'],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50, 'description' => 'view=list : 20 par défaut.'],
            ],
            'required' => ['view'],
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
        if ($input['view'] === 'detail') {
            return $this->detail($context, (int) ($input['import_id'] ?? 0));
        }

        $query = Import::query()->where('project_id', $context->project->id);
        match ($input['state'] ?? null) {
            'processing' => $query->where('is_processing', true),
            'done' => $query->where('is_processing', false)->whereNotNull('processed_at'),
            'to_launch' => $query->where('is_processing', false)->whereNull('processed_at'),
            default => null,
        };

        $total = (clone $query)->count();
        $imports = $query->orderByDesc('id')->limit(max(1, min(50, (int) ($input['limit'] ?? 20))))->get();

        return ToolResult::ok(
            ['total' => $total, 'imports' => $imports->map(fn (Import $import) => $this->summary($import))->all()],
            'Imports de prospects',
            $imports->count()
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }

    protected function detail(AgentExecutionContext $context, int $id): ToolResult
    {
        $import = $id > 0 ? Import::query()->where('project_id', $context->project->id)->find($id) : null;

        if (!$import) {
            return ToolResult::error("Import $id introuvable dans ce projet, ou non accessible pour cet utilisateur.", ToolResult::NOT_FOUND);
        }

        $headers = array_values((array) $import->headers);
        $mapping = (array) $import->mapping;
        $mapped = [];
        $unmapped = [];

        foreach ($headers as $index => $header) {
            if ($header === null || trim((string) $header) === '') {
                continue;
            }
            $label = mb_substr(strip_tags((string) $header), 0, 80);
            empty($mapping[$index]) ? $unmapped[] = $label : $mapped[] = $label;
        }

        return ToolResult::ok(
            $this->summary($import) + [
                'columns_mapped' => $mapped,
                'columns_not_mapped' => $unmapped,
                'duplicates_fields' => array_values((array) $import->duplicates_fields),
                'selected_sheets' => $import->selected_sheets,
                'prospects_created' => $import->prospects()->count(),
            ],
            "Import {$import->id}",
            1
        );
    }

    /**
     * One import without its content.
     */
    protected function summary(Import $import): array
    {
        return [
            'id' => $import->id,
            'name' => mb_substr(strip_tags((string) $import->name), 0, 120),
            'source' => $import->source,
            'state' => $this->state($import),
            'rows' => $import->rows_count,
            'columns' => $import->cols_count,
            // processed_at is not cast by the model: it comes as text
            'processed_at' => $import->processed_at ? \Carbon\Carbon::parse($import->processed_at)->toIso8601String() : null,
            'sync_enabled' => (bool) $import->sync_enabled,
            'last_synced_at' => optional($import->last_synced_at)->toIso8601String(),
        ];
    }

    public static function state(Import $import): string
    {
        return match (true) {
            (bool) $import->is_processing => 'processing',
            $import->processed_at !== null => 'done',
            default => 'to_launch',
        };
    }
}
