<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Models\AgentReport;

/**
 * Saves a document the agent produced (a report, an analysis, a summary), to
 * be shown next to the conversation like a Claude "artifact".
 *
 * It changes nothing in the CRM: it stores the agent's own output, for its
 * owner, in the project of the run. That is why it is a "read" tool, usable by
 * a read-only agent, and needs no ability. The content is untrusted text for
 * whoever displays it (the screen renders it as safe Markdown).
 */
class ReportCreateTool implements AgentToolInterface
{
    public const MAX_CHARS = 60000;

    public function key(): string
    {
        return 'report_create';
    }

    public function description(): string
    {
        return "Enregistre un document (rapport, analyse, synthèse) qui s'affiche à côté de la conversation. "
            . "À utiliser pour tout livrable un peu long ou à conserver : rapport d'activité, comparaison de périodes, plan d'action. "
            . "Le contenu est du Markdown (titres, listes, tableaux). Mets dans le document tout ce qui est utile ; dans ta réponse, résume en quelques lignes et renvoie vers le document. "
            . "N'y mets que des chiffres obtenus avec les outils du CRM.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string', 'maxLength' => 200, 'description' => 'Titre du document.'],
                'content' => ['type' => 'string', 'maxLength' => self::MAX_CHARS, 'description' => 'Le document en Markdown.'],
            ],
            'required' => ['title', 'content'],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'report';
    }

    public function risk(): string
    {
        return self::READ;
    }

    public function ability(): ?string
    {
        return null;
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        if (!$context->agentId) {
            return ToolResult::error('Ce document ne peut être enregistré que pendant une conversation avec un agent.', ToolResult::UNAVAILABLE);
        }

        $title = trim($input['title']);
        $content = trim($input['content']);

        if ($title === '' || $content === '') {
            return ToolResult::error('Le titre et le contenu ne peuvent pas être vides.', ToolResult::INVALID_INPUT);
        }

        $report = AgentReport::create([
            'agent_id' => $context->agentId,
            'run_id' => $context->runId,
            'user_id' => $context->owner->id,
            'project_id' => $context->project->id,
            'title' => mb_substr($title, 0, 200),
            'format' => AgentReport::MARKDOWN,
            'content' => $content,
        ]);

        return ToolResult::ok(
            ['report_id' => $report->id, 'title' => $report->title, 'note' => "Le document est affiché à l'utilisateur à côté de la conversation."],
            "Document {$report->id} enregistré",
            1
        );
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }
}
