<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Models\Document;

/**
 * The document templates (PDF) of the project the user may use. The content of the pages is never given: it is the
 * business of the editor of the interface.
 */
class DocumentTemplatesTool implements AgentToolInterface
{
    public const TARGETS = ['prospect', 'invoice'];

    public function key(): string
    {
        return 'document_templates';
    }

    public function description(): string
    {
        return "Liste les modèles de documents (PDF) du projet que l'utilisateur peut utiliser : nom, destination (for = prospect pour une fiche, invoice pour une facture), description, "
            . "et si le PDF généré est conservé dans un dossier de la fiche (keeps_a_copy). Donne les identifiants à utiliser avec document_generate.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'for' => ['type' => 'string', 'enum' => self::TARGETS, 'description' => 'Limiter à une destination.'],
                'search' => ['type' => 'string', 'maxLength' => 100, 'description' => 'Une partie du nom.'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'document';
    }

    public function risk(): string
    {
        return self::READ;
    }

    public function ability(): ?string
    {
        return 'projectDocumentView';
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        // The usual access rule of the CRM applies: a template not given to the user is not listed
        $query = Document::query()->where('documents.project_id', $context->project->id);

        if (!empty($input['for'])) {
            $query->where('documents.for', $input['for']);
        }
        if (($search = trim((string) ($input['search'] ?? ''))) !== '') {
            $query->where('documents.name', 'like', '%' . str_replace(['%', '_'], ['\\%', '\\_'], mb_substr($search, 0, 100)) . '%');
        }

        $documents = $query->orderBy('documents.name')->limit(50)->get();

        return ToolResult::ok(
            ['templates' => $documents->map(fn (Document $document) => [
                'id' => $document->id,
                'name' => mb_substr(strip_tags((string) $document->name), 0, 120),
                'for' => $document->for,
                'description' => $document->description ? mb_substr(strip_tags((string) $document->description), 0, 200) : null,
                'keeps_a_copy' => (bool) $document->folder_id,
            ])->all()],
            'Modèles de documents',
            $documents->count()
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }
}
