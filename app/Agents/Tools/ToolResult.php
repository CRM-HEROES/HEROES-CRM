<?php

namespace App\Agents\Tools;

/**
 * What a tool returns to the model. One shape for every tool:
 *
 *   {"success": true,  ...data}
 *   {"success": false, "error": {"code": "...", "message": "..."}, "retryable": false}
 *
 * Everything that comes from the CRM (names, notes, messages, imported rows)
 * is untrusted text: it is wrapped so that the model reads it as data, never
 * as an instruction.
 */
final class ToolResult
{
    public const PERMISSION_DENIED = 'PERMISSION_DENIED';
    public const NOT_FOUND = 'NOT_FOUND';
    public const INVALID_INPUT = 'INVALID_INPUT';
    public const VALIDATION_FAILED = 'VALIDATION_FAILED';
    public const CONFIRMATION_REQUIRED = 'CONFIRMATION_REQUIRED';
    public const LIMIT_EXCEEDED = 'LIMIT_EXCEEDED';
    public const UNAVAILABLE = 'UNAVAILABLE';
    public const INTERNAL_ERROR = 'INTERNAL_ERROR';
    public const ERROR = 'ERROR';

    private function __construct(
        public readonly array $data,
        public readonly string $summary,
        public readonly ?int $rows,
        public readonly bool $isError,
        public readonly ?string $code = null,
        public readonly bool $retryable = false,
    ) {
    }

    /**
     * @param  array  $data  returned to the model
     * @param  string  $summary  kept in the audit log (no personal data)
     */
    public static function ok(array $data, string $summary = '', ?int $rows = null): self
    {
        return new self($data, $summary, $rows, false);
    }

    /**
     * @param  string  $code  one of the constants above: it lets the model choose another strategy
     * @param  bool  $retryable  would the same call possibly work later?
     */
    public static function error(string $message, string $code = self::ERROR, bool $retryable = false): self
    {
        return new self(['error' => $message], $message, null, true, $code, $retryable);
    }

    /**
     * The text of the tool_result block
     */
    public function toContent(): string
    {
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE;

        if ($this->isError) {
            return (string) json_encode([
                'success' => false,
                'error' => ['code' => $this->code, 'message' => $this->data['error'] ?? ''],
                'retryable' => $this->retryable,
            ], $flags);
        }

        $json = (string) json_encode(['success' => true] + $this->data, $flags);

        // The closing tag cannot be forged from inside the data
        return '<crm_data untrusted="true">' . str_replace('</crm_data>', '<\/crm_data>', $json) . '</crm_data>';
    }
}
