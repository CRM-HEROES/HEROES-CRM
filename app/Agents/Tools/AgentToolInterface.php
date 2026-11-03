<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;

/**
 * A tool an agent can call.
 *
 * The CRM stays the only authority on the rights: a tool never decides alone
 * what is allowed. The engine checks, before handle() is called, the tool is
 * granted to the agent, the autonomy of the agent allows its risk, and the
 * owner has the CRM ability the tool declares (see PermissionGate).
 *
 * handle() runs inside AgentExecutionContext::run(): the authenticated user is
 * the owner of the agent and the project is the one of the run. It must take
 * the project from the context, never from $input, and must check that every
 * identifier it is given belongs to that project.
 */
interface AgentToolInterface
{
    public const READ = 'read';
    public const WRITE = 'write';
    public const COMMUNICATION = 'communication';
    public const DESTRUCTIVE = 'destructive';

    public const RISKS = [self::READ, self::WRITE, self::COMMUNICATION, self::DESTRUCTIVE];

    /**
     * Unique name, sent to the model (^[a-zA-Z0-9_-]{1,64}$)
     */
    public function key(): string;

    /**
     * What the tool does and when to use it, written for the model
     */
    public function description(): string;

    /**
     * JSON Schema of the input (additionalProperties: false)
     */
    public function inputSchema(): array;

    /**
     * Module of the CRM the tool belongs to (prospect, call, stat, ...)
     */
    public function module(): string;

    /**
     * read | write | communication | destructive
     */
    public function risk(): string;

    /**
     * CRM ability the owner must have in the project (ProjectPolicy), e.g.
     * "prospectView". Null only for tools that read nothing of the CRM.
     */
    public function ability(): ?string;

    public function handle(AgentExecutionContext $context, array $input): ToolResult;

    /**
     * What the call would change, for the human who approves it. Computed from
     * the CRM by the server (never written by the model); read tools return [].
     */
    public function preview(AgentExecutionContext $context, array $input): array;
}
