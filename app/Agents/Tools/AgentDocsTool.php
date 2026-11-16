<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Models\AgentDocument;

class AgentDocsTool implements AgentToolInterface
{
    public function key(): string
    {
        return 'agent_docs';
    }

    public function description(): string
    {
        return 'Liste les documents de référence de l’agent courant, ou renvoie le résumé structuré d’un document via document_id. Ne complète pas les informations absentes.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'document_id' => ['type' => 'integer', 'description' => 'Identifiant d’un document de cet agent. Omettre pour lister les documents.'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'agent';
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
            return ToolResult::error('L’agent courant est introuvable.', ToolResult::UNAVAILABLE);
        }

        $query = AgentDocument::query()->where('agent_id', $context->agentId)
            ->whereHas('agent', fn ($agents) => $agents->where('user_id', $context->owner->id));

        if (isset($input['document_id'])) {
            $document = $query->find($input['document_id']);
            if (!$document) {
                return ToolResult::error('Document de référence introuvable pour cet agent.', ToolResult::NOT_FOUND);
            }

            return ToolResult::ok([
                'id' => $document->id,
                'name' => $document->name,
                'status' => $document->status,
                'summary' => $document->summary,
            ], 'Résumé du document de référence', 1);
        }

        return ToolResult::ok($query->orderByDesc('id')->get(['id', 'name', 'status'])->toArray(), 'Documents de référence', $query->count());
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }
}
