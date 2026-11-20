<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Writes\InternalApi;

/**
 * Installs a ready-made template of document (from the catalog of the CRM) in the project: a new template with its pages and
 * fields, to adjust in the editor. The route of the CRM asks no right: the tool asks the one of adding a document.
 */
class DocumentTemplateInstallTool implements AgentToolInterface, ChecksBeforeApproval
{
    public function __construct(protected InternalApi $api)
    {
    }

    public function key(): string
    {
        return 'document_template_install';
    }

    public function description(): string
    {
        return "Installe dans le projet un modèle de document prêt à l'emploi du catalogue (document_templates avec catalog=true) : un nouveau modèle avec ses pages et champs, à ajuster ensuite dans l'éditeur. "
            . "Il ne remplace rien : installer deux fois crée deux modèles.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => ['template' => ['type' => 'string', 'maxLength' => 40, 'description' => 'La clé du modèle dans le catalogue.']],
            'required' => ['template'],
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
        return 'projectDocumentAdd';
    }

    public function precheck(AgentExecutionContext $context, array $input): ?ToolResult
    {
        return isset(((array) config('document.templates'))[$input['template']])
            ? null
            : ToolResult::error("Modèle « {$input['template']} » inconnu : le catalogue se lit avec document_templates (catalog=true).", ToolResult::NOT_FOUND);
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        if ($error = $this->precheck($context, $input)) {
            return $error;
        }

        $response = $this->api->call($context, 'POST', 'document-template/' . rawurlencode($input['template']));

        if (!$response->ok()) {
            return $response->toError();
        }

        return ToolResult::ok(['done' => true, 'document_id' => $response->json['id'] ?? null, 'next' => "Ajuste-le dans l'éditeur des documents ; choisis un dossier pour que les PDF soient conservés."], 'Modèle de document installé', 1);
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        $template = ((array) config('document.templates'))[$input['template'] ?? ''] ?? null;

        return [
            'action' => 'Installer un modèle de document prêt à l\'emploi',
            'modèle' => $template ? mb_substr(strip_tags((string) ($template['name'] ?? '')), 0, 120) : ($input['template'] ?? '?'),
        ];
    }
}
