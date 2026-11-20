<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;
use App\Agents\Entities\ReadGateway;
use App\Agents\Writes\InternalApi;

/**
 * An action on ONE record (or a new one), defined by data, through a route of
 * the CRM: "create a prospect", "update a prospect", "book an appointment",
 * "send an SMS"...
 *
 * Definition keys:
 *   key, description, risk, ability, module
 *   schema, required   input properties (JSON Schema)
 *   prospect           name of the input property holding the prospect id; the
 *                      prospect must be one the owner can see (else NOT_FOUND)
 *   editable           true: a processed prospect is refused (the CRM locks it)
 *   request            fn (context, input, ?Prospect): array [method, path, body] | ToolResult
 *   describe           fn (context, input): array — what will be done, for the approval
 *   result             fn (array $json): array — what the model gets back (default: ids only)
 *   verify             fn (context, array $json): ?ToolResult — what the CRM did after the route answered (an operator
 *                      that refused an SMS is only known by reading it back); an error is given to the model
 */
class ProspectRecordTool implements AgentToolInterface, ChecksBeforeApproval
{
    public function __construct(
        protected array $definition,
        protected InternalApi $api,
        protected ReadGateway $reader,
    ) {
    }

    public function key(): string
    {
        return $this->definition['key'];
    }

    public function description(): string
    {
        return $this->definition['description'];
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => $this->definition['schema'] ?? [],
            'required' => $this->definition['required'] ?? [],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return $this->definition['module'] ?? 'prospect';
    }

    public function risk(): string
    {
        return $this->definition['risk'];
    }

    public function ability(): ?string
    {
        return $this->definition['ability'];
    }

    public function precheck(AgentExecutionContext $context, array $input): ?ToolResult
    {
        $prepared = $this->prepare($context, $input);

        return $prepared instanceof ToolResult ? $prepared : null;
    }

    /**
     * Everything that can refuse the request before the route of the CRM is called: [prospect, [method, path, body]], or the refusal.
     *
     * @return ToolResult|array{0: ?\App\Models\Prospect, 1: array}
     */
    protected function prepare(AgentExecutionContext $context, array $input): ToolResult|array
    {
        $prospect = null;

        if ($property = $this->definition['prospect'] ?? null) {
            $found = $this->reader->visibleProspectIds($context, [(int) $input[$property]], false);

            if (!$found['ids']) {
                return ToolResult::error("Prospect {$input[$property]} introuvable dans ce projet, ou non accessible pour cet utilisateur.", ToolResult::NOT_FOUND);
            }

            $prospect = \App\Models\Prospect::query()->find($found['ids'][0]);

            if (($this->definition['editable'] ?? false) && $prospect->processed_at) {
                return ToolResult::error('Cette fiche est traitée : le CRM ne permet plus de la modifier.', ToolResult::PERMISSION_DENIED);
            }
        }

        $request = ($this->definition['request'])($context, $input, $prospect);

        return $request instanceof ToolResult ? $request : [$prospect, $request];
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $prepared = $this->prepare($context, $input);

        if ($prepared instanceof ToolResult) {
            return $prepared;
        }

        [$prospect, [$method, $path, $body]] = $prepared;
        $response = $this->api->call($context, $method, $path, $body);

        if (!$response->ok()) {
            return $response->toError();
        }

        if (isset($this->definition['verify']) && ($problem = ($this->definition['verify'])($context, $response->json ?? []))) {
            return $problem;
        }

        $data = isset($this->definition['result'])
            ? ($this->definition['result'])($response->json ?? [])
            : array_filter(['id' => $response->json['id'] ?? null, 'prospect_id' => $prospect?->id, 'message' => $response->json['message'] ?? null]);

        return ToolResult::ok(['done' => true] + $data, "{$this->key()} exécuté", 1);
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        $about = [];

        if ($property = $this->definition['prospect'] ?? null) {
            $prospect = \App\Models\Prospect::query()->where('project_id', $context->project->id)->find($input[$property] ?? 0);
            $about = $prospect ? ['prospect' => trim("{$prospect->first_name} {$prospect->last_name}") . " #{$prospect->id}"] : [];
        }

        return $about + ($this->definition['describe'])($context, $input);
    }
}
