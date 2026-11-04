<?php

namespace App\Agents\Claude;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\APITimeoutException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\BadRequestException;
use Anthropic\Core\Exceptions\InternalServerException;
use Anthropic\Core\Exceptions\PermissionDeniedException;
use Anthropic\Core\Exceptions\RateLimitException;
use Psr\Http\Client\ClientInterface;

/**
 * ClaudeGateway on the official Anthropic SDK.
 *
 * The SDK already retries the rate limits (429), the timeouts and the 5xx
 * errors, honouring `retry-after`: nothing is retried here.
 */
class AnthropicGateway implements ClaudeGateway
{
    public function __construct(
        protected string $apiKey,
        protected ?ClientInterface $transporter = null,
        protected float $timeout = 300.0,
        protected int $maxRetries = 2,
        // null: agents.strict_tools
        protected ?bool $strict = null,
    ) {
    }

    public function send(array $request): ClaudeResponse
    {
        $options = ['timeout' => $this->timeout, 'maxRetries' => $this->maxRetries];

        if ($this->transporter) {
            $options['transporter'] = $this->transporter;
        }

        $params = [
            'model' => $request['model'],
            'maxTokens' => $request['max_tokens'],
            'messages' => $this->messages($request['messages']),
            // Always sent: the default effort is not the same on every model
            'outputConfig' => ['effort' => $request['effort'] ?? config('agents.default_effort')],
        ];

        if (!empty($request['system'])) {
            $params['system'] = $this->system($request['system']);
        }

        if (!empty($request['tools'])) {
            $params['tools'] = $this->tools($request['tools']);
        }

        try {
            $message = (new Client(apiKey: $this->apiKey, requestOptions: $options))
                ->messages
                ->create(...$params);
        } catch (AuthenticationException|PermissionDeniedException $e) {
            throw ClaudeException::invalidKey($e);
        } catch (RateLimitException $e) {
            throw ClaudeException::rateLimited($e);
        } catch (BadRequestException $e) {
            throw ClaudeException::badRequest($e);
        } catch (InternalServerException $e) {
            throw ClaudeException::overloaded($e);
        } catch (APIStatusException $e) {
            // 529 "overloaded" and the other statuses
            throw $e->status === 529 ? ClaudeException::overloaded($e) : ClaudeException::failed($e);
        } catch (APITimeoutException $e) {
            throw ClaudeException::timeout($e);
        } catch (APIConnectionException $e) {
            throw ClaudeException::unavailable($e);
        }

        $usage = $message->usage;

        return new ClaudeResponse(
            id: $message->id,
            model: $message->model,
            // The blocks as the API sent them, to be stored and replayed as they are
            content: json_decode(json_encode($message->content), true) ?? [],
            stopReason: $message->stopReason,
            inputTokens: (int) $usage->inputTokens,
            outputTokens: (int) $usage->outputTokens,
            cacheReadTokens: (int) ($usage->cacheReadInputTokens ?? 0),
            cacheWriteTokens: (int) ($usage->cacheCreationInputTokens ?? 0),
            refusalCategory: $message->stopDetails?->category,
        );
    }

    protected function system(array $segments): array
    {
        return array_map(function (array $segment) {
            $block = ['type' => 'text', 'text' => $segment['text']];

            if (!empty($segment['cache'])) {
                $block['cacheControl'] = ['type' => 'ephemeral'];
            }

            return $block;
        }, $segments);
    }

    protected function tools(array $tools): array
    {
        $strict = $this->strict ?? (bool) config('agents.strict_tools', false);

        return array_map(function (array $tool) use ($strict) {
            $definition = [
                'name' => $tool['name'],
                'description' => $tool['description'],
                'inputSchema' => $this->schema($tool['schema'], $strict),
            ];

            // The arguments are then guaranteed to follow the schema
            if ($strict) {
                $definition['strict'] = true;
            }

            return $definition;
        }, $tools);
    }

    /**
     * The schema as sent to the API.
     *
     * Strict tool use is limited: it does not accept numerical, string or array
     * size constraints (the PHP SDK does not strip them, the Python and
     * TypeScript ones do) and the optional parameters of ALL the tools together
     * are limited to 24. In strict mode the constraints are removed and
     * written in the description, so the model still knows the limits. In both
     * modes the engine enforces the full schema itself (see InputValidator).
     */
    protected function schema(array $schema, bool $strict): array
    {
        $notes = [];

        foreach ($strict ? ['minimum' => 'au moins %s', 'maximum' => 'au plus %s', 'minLength' => '%s caractères au moins', 'maxLength' => '%s caractères au plus', 'minItems' => '%s éléments au moins', 'maxItems' => '%s éléments au plus', 'multipleOf' => 'multiple de %s'] : [] as $keyword => $text) {
            if (array_key_exists($keyword, $schema)) {
                $notes[] = sprintf($text, $schema[$keyword]);
                unset($schema[$keyword]);
            }
        }

        if ($notes) {
            $schema['description'] = trim(($schema['description'] ?? '') . ' (' . implode(', ', $notes) . ')');
        }

        if (isset($schema['properties']) && is_array($schema['properties'])) {
            foreach ($schema['properties'] as $name => $property) {
                if (is_array($property)) {
                    $schema['properties'][$name] = $this->schema($property, $strict);
                }
            }
        }

        if (isset($schema['items']) && is_array($schema['items'])) {
            $schema['items'] = $this->schema($schema['items'], $strict);
        }

        // An empty list would be sent as [] instead of {}
        if (($schema['type'] ?? null) === 'object' && empty($schema['properties'])) {
            $schema['properties'] = new \stdClass();
        }

        return $schema;
    }

    /**
     * The stored history, ready to be replayed.
     */
    protected function messages(array $messages): array
    {
        return array_map(function (array $message) {
            if (is_array($message['content'])) {
                $message['content'] = array_map(function ($block) {
                    // A tool call without argument is stored as [] and must go back as {}
                    if (is_array($block) && ($block['type'] ?? null) === 'tool_use' && ($block['input'] ?? null) === []) {
                        $block['input'] = new \stdClass();
                    }

                    return $block;
                }, $message['content']);
            }

            return ['role' => $message['role'], 'content' => $message['content']];
        }, $messages);
    }
}
