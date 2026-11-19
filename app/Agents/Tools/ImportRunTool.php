<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Writes\ConfirmsLargeActions;
use App\Agents\Writes\InternalApi;
use App\Models\Import;

/**
 * Launches (or replays) an import that already exists and is mapped, as the "Valider l'import" / "Re importer" button
 * does. The file itself is uploaded and mapped by a person: the agent cannot create an import from nothing.
 *
 * Creating prospects in bulk is not something to do lightly: the number of rows drives the confirmation of the user.
 */
class ImportRunTool implements AgentToolInterface, ConfirmsLargeActions
{
    public function __construct(protected InternalApi $api)
    {
    }

    public function key(): string
    {
        return 'import_run';
    }

    public function description(): string
    {
        return "Lance un import de prospects déjà créé (fichier ou Google Sheets) dont les colonnes sont reliées, comme « Valider l'import » ou « Re importer ». "
            . "Vérifie son état avec import_status d'abord : un import en cours ne se relance pas, et il faut au moins une colonne reliée. "
            . 'Au-delà de ' . config('agents.bulk_confirm_threshold', 20) . ' lignes, l\'utilisateur doit valider avant l\'exécution. '
            . "Le résultat se suit ensuite avec import_status.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => ['import_id' => ['type' => 'integer']],
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

    public function affectedCount(AgentExecutionContext $context, array $input): int
    {
        return (int) ($this->find($context, (int) ($input['import_id'] ?? 0))?->rows_count ?? 0);
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $import = $this->find($context, (int) $input['import_id']);

        if (!$import) {
            return ToolResult::error("Import {$input['import_id']} introuvable dans ce projet, ou non accessible pour cet utilisateur.", ToolResult::NOT_FOUND);
        }
        if (!in_array($import->source, ['file', 'google_sheets'], true)) {
            return ToolResult::error('Seuls les imports de fichier ou de Google Sheets se lancent ici.', ToolResult::INVALID_INPUT);
        }
        if ($import->is_processing) {
            return ToolResult::error('Cet import est déjà en cours.', ToolResult::INVALID_INPUT);
        }
        if (!array_filter((array) $import->mapping)) {
            return ToolResult::error('Aucune colonne n\'est reliée à un champ : le mapping se fait dans l\'interface, onglet « Mapping ».', ToolResult::INVALID_INPUT);
        }

        $response = $this->api->call($context, 'PUT', "import/{$import->id}", ['is_processing' => true]);

        if (!$response->ok()) {
            return $response->toError();
        }

        return ToolResult::ok(
            ['import_id' => $import->id, 'state' => 'processing', 'rows' => $import->rows_count, 'follow_with' => 'import_status'],
            "Import {$import->id} lancé",
            (int) $import->rows_count
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        $import = $this->find($context, (int) ($input['import_id'] ?? 0));

        return $import ? [
            'summary' => "Lancer l'import « " . mb_substr(strip_tags((string) $import->name), 0, 80) . " » ({$import->rows_count} lignes)",
        ] : [];
    }

    protected function find(AgentExecutionContext $context, int $id): ?Import
    {
        return $id > 0 ? Import::query()->where('project_id', $context->project->id)->find($id) : null;
    }
}
