<?php

namespace App\Agents\Writes;

use App\Agents\Tools\ToolResult;

/**
 * The answer of a route of the CRM, read for a tool.
 */
final class InternalResponse
{
    public function __construct(
        public readonly int $status,
        public readonly ?array $json,
        public readonly string $body,
    ) {
    }

    public function ok(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    /**
     * The error to give the model: a code it can act on and a message written
     * by the CRM (validation messages are useful, stack traces never leave).
     */
    public function toError(): ToolResult
    {
        $message = is_array($this->json) ? ($this->json['message'] ?? null) : null;

        return match (true) {
            $this->status === 422 => ToolResult::error(
                'Le CRM refuse ces données : ' . $this->validationText($message),
                ToolResult::VALIDATION_FAILED
            ),
            $this->status === 404 => ToolResult::error(
                'Introuvable ou non accessible dans ce projet' . ($message && $message !== 'Not Found' ? " ({$message})." : '.'),
                ToolResult::NOT_FOUND
            ),
            in_array($this->status, [401, 403], true) => ToolResult::error('Action non autorisée pour cet utilisateur.', ToolResult::PERMISSION_DENIED),
            $this->status === 400 => ToolResult::error('Demande refusée par le CRM' . (is_string($this->body) && strlen($this->body) < 200 ? " : {$this->body}" : '.'), ToolResult::INVALID_INPUT),
            $this->status === 429 => ToolResult::error('Trop de requêtes, réessaie plus tard.', ToolResult::UNAVAILABLE, true),
            default => ToolResult::error("Le CRM a répondu avec une erreur ({$this->status}).", ToolResult::INTERNAL_ERROR, $this->status >= 500),
        };
    }

    protected function validationText(?string $message): string
    {
        $errors = is_array($this->json) ? ($this->json['errors'] ?? []) : [];
        $lines = [];

        foreach ($errors as $field => $messages) {
            $lines[] = "$field : " . implode(' ', (array) $messages);
        }

        return $lines ? implode(' ; ', array_slice($lines, 0, 5)) : ($message ?: 'données invalides.');
    }
}
