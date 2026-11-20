<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Entities\ReadGateway;
use App\Agents\Writes\InternalApi;
use App\Models\Document;

/**
 * Generates the PDF of a prospect from a template, as "Document" of the fiche does, and keeps it in the folder of the
 * template on the fiche of the prospect. The agent never reads the PDF (it holds personal data): it only knows it was made.
 *
 * A template without a folder would render a PDF nobody can get back, so it is refused.
 */
class DocumentGenerateTool implements AgentToolInterface, ChecksBeforeApproval
{
    public function __construct(protected InternalApi $api, protected ReadGateway $reader)
    {
    }

    public function key(): string
    {
        return 'document_generate';
    }

    public function description(): string
    {
        return "Génère le PDF d'un prospect à partir d'un modèle de document (document_templates, for = prospect) et le range dans le dossier du modèle sur la fiche du prospect. "
            . "Un modèle sans dossier (keeps_a_copy = false) est refusé : le PDF ne serait conservé nulle part. Le contenu du PDF n'est pas lu par l'agent.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'prospect_id' => ['type' => 'integer'],
                'document_id' => ['type' => 'integer', 'description' => 'Modèle de document (document_templates).'],
            ],
            'required' => ['prospect_id', 'document_id'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'document';
    }

    public function risk(): string
    {
        return self::WRITE;
    }

    public function ability(): ?string
    {
        return 'projectDocumentView';
    }

    public function precheck(AgentExecutionContext $context, array $input): ?ToolResult
    {
        $checked = $this->check($context, $input);

        return $checked instanceof ToolResult ? $checked : null;
    }

    /**
     * The template and the id of the prospect, or the refusal.
     *
     * @return ToolResult|array{0: Document, 1: int}
     */
    protected function check(AgentExecutionContext $context, array $input): ToolResult|array
    {
        $document = $this->document($context, (int) $input['document_id']);

        if (!$document) {
            return ToolResult::error("Modèle {$input['document_id']} introuvable dans ce projet, ou non accessible pour cet utilisateur.", ToolResult::NOT_FOUND);
        }
        if ($document->for !== 'prospect') {
            return ToolResult::error('Ce modèle n\'est pas destiné aux prospects : seuls les modèles « prospect » se génèrent ici.', ToolResult::INVALID_INPUT);
        }
        if (!$document->folder_id) {
            return ToolResult::error('Ce modèle n\'a pas de dossier : le PDF ne serait conservé nulle part. Un dossier se choisit dans l\'éditeur du modèle (« Générer dans le dossier »).', ToolResult::INVALID_INPUT);
        }

        $visible = $this->reader->visibleProspectIds($context, [$input['prospect_id']], false);
        if (!$visible['ids']) {
            return ToolResult::error("Prospect {$input['prospect_id']} introuvable dans ce projet, ou non accessible pour cet utilisateur.", ToolResult::NOT_FOUND);
        }

        return [$document, $visible['ids'][0]];
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $checked = $this->check($context, $input);

        if ($checked instanceof ToolResult) {
            return $checked;
        }

        [$document, $prospectId] = $checked;
        $response = $this->api->call($context, 'GET', "prospect/$prospectId/document/{$document->id}", [], true);

        if (!$response->ok()) {
            return $response->toError();
        }

        return ToolResult::ok(
            ['prospect_id' => $prospectId, 'document_id' => $document->id, 'folder_id' => $document->folder_id, 'generated' => true],
            "PDF « {$document->name} » généré pour le prospect $prospectId",
            1
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        $name = Document::query()->where('documents.project_id', $context->project->id)->whereKey((int) ($input['document_id'] ?? 0))->value('name');

        return [
            'action' => 'Générer le PDF et le ranger sur la fiche',
            'document' => $name ? mb_substr(strip_tags($name), 0, 120) : '#' . ($input['document_id'] ?? '?'),
            'prospect_id' => $input['prospect_id'] ?? null,
        ];
    }

    protected function document(AgentExecutionContext $context, int $id): ?Document
    {
        return $id > 0 ? Document::query()->where('documents.project_id', $context->project->id)->find($id) : null;
    }
}
