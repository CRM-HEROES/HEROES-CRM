<?php

namespace Tests\Feature;

use App\Agents\Claude\AnthropicGateway;
use App\Agents\Claude\ClaudeException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

/**
 * The adapter on the real SDK, with a fake HTTP transport: nothing leaves the
 * machine and no key is used.
 */
class AnthropicGatewayTest extends TestCase
{
    protected function transport(int $status, array $body): ClientInterface
    {
        return new class($status, $body) implements ClientInterface {
            /** @var string[] */
            public array $bodies = [];

            /** @var array<int, array> */
            public array $headers = [];

            public function __construct(private int $status, private array $body)
            {
            }

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                $this->bodies[] = (string) $request->getBody();
                $this->headers[] = $request->getHeaders();

                return new Response($this->status, ['Content-Type' => 'application/json'], json_encode($this->body));
            }
        };
    }

    protected function message(array $content, string $stop = 'end_turn', array $extra = []): array
    {
        return $extra + [
            'id' => 'msg_1',
            'type' => 'message',
            'role' => 'assistant',
            'model' => 'claude-opus-5-5',
            'content' => $content,
            'stop_reason' => $stop,
            'stop_sequence' => null,
            'usage' => ['input_tokens' => 10, 'output_tokens' => 5, 'cache_creation_input_tokens' => 3, 'cache_read_input_tokens' => 7],
        ];
    }

    protected function request(array $override = []): array
    {
        return $override + [
            'model' => 'claude-opus-5-5',
            'system' => [['text' => 'RÈGLES', 'cache' => true]],
            'tools' => [['name' => 'crm_search', 'description' => 'Cherche.', 'schema' => ['type' => 'object', 'properties' => ['q' => ['type' => 'string']], 'additionalProperties' => false]]],
            'messages' => [['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Bonjour']]]],
            'max_tokens' => 1000,
            'effort' => 'medium',
        ];
    }

    public function test_the_request_follows_the_api_shape(): void
    {
        $transport = $this->transport(200, $this->message([['type' => 'text', 'text' => 'Salut']]));

        (new AnthropicGateway('sk-ant-secret', $transport, 30, 0))->send($this->request());

        $body = json_decode($transport->bodies[0], true);

        $this->assertSame('claude-opus-5-5', $body['model']);
        $this->assertSame(1000, $body['max_tokens']);
        // The effort is always sent: its default is not the same on every model
        $this->assertSame('medium', $body['output_config']['effort']);
        $this->assertSame(['type' => 'ephemeral'], $body['system'][0]['cache_control']);
        $this->assertSame('crm_search', $body['tools'][0]['name']);
        // Strict mode is off by default: the engine validates the calls itself
        $this->assertArrayNotHasKey('strict', $body['tools'][0]);
        $this->assertSame('string', $body['tools'][0]['input_schema']['properties']['q']['type']);
        // No sampling parameter, no thinking budget, no prefill: the models reject them
        $this->assertArrayNotHasKey('temperature', $body);
        $this->assertArrayNotHasKey('thinking', $body);
        $this->assertSame('user', end($body['messages'])['role']);
        // The key travels in a header, never in the body
        $this->assertStringNotContainsString('sk-ant-secret', $transport->bodies[0]);
    }

    public function test_the_constraints_strict_mode_refuses_are_moved_to_the_description(): void
    {
        $transport = $this->transport(200, $this->message([['type' => 'text', 'text' => 'x']]));
        $tool = ['name' => 'search', 'description' => 'Cherche.', 'schema' => [
            'type' => 'object',
            'properties' => [
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50, 'description' => 'Nombre de lignes.'],
                'name' => ['type' => 'string', 'maxLength' => 20],
                'ids' => ['type' => 'array', 'items' => ['type' => 'integer', 'minimum' => 0], 'maxItems' => 5],
                'filters' => ['type' => 'object', 'properties' => ['age' => ['type' => 'integer', 'maximum' => 99]], 'additionalProperties' => false],
            ],
            'additionalProperties' => false,
        ]];

        (new AnthropicGateway('k', $transport, 30, 0, true))->send($this->request(['tools' => [$tool]]));

        $sent = json_decode($transport->bodies[0], true)['tools'][0];
        $this->assertTrue($sent['strict']);
        $sent = $sent['input_schema'];
        $json = $transport->bodies[0];

        foreach (['"minimum"', '"maximum"', '"maxLength"', '"maxItems"'] as $keyword) {
            $this->assertStringNotContainsString($keyword, $json);
        }

        $this->assertSame('Nombre de lignes. (au moins 1, au plus 50)', $sent['properties']['limit']['description']);
        $this->assertSame('(20 caractères au plus)', $sent['properties']['name']['description']);
        $this->assertSame('(5 éléments au plus)', $sent['properties']['ids']['description']);
        $this->assertSame('(au plus 99)', $sent['properties']['filters']['properties']['age']['description']);
        // The rest of the schema is untouched
        $this->assertFalse($sent['additionalProperties']);
        $this->assertSame('integer', $sent['properties']['limit']['type']);
    }

    public function test_without_strict_mode_the_schema_keeps_its_constraints(): void
    {
        $transport = $this->transport(200, $this->message([['type' => 'text', 'text' => 'x']]));
        $tool = ['name' => 'search', 'description' => 'Cherche.', 'schema' => [
            'type' => 'object',
            'properties' => ['limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50]],
            'additionalProperties' => false,
        ]];

        (new AnthropicGateway('k', $transport, 30, 0, false))->send($this->request(['tools' => [$tool]]));

        $sent = json_decode($transport->bodies[0], true)['tools'][0];

        $this->assertArrayNotHasKey('strict', $sent);
        $this->assertSame(1, $sent['input_schema']['properties']['limit']['minimum']);
        $this->assertSame(50, $sent['input_schema']['properties']['limit']['maximum']);
    }

    public function test_a_tool_without_argument_is_sent_with_an_object_schema(): void
    {
        $transport = $this->transport(200, $this->message([['type' => 'text', 'text' => 'x']]));
        $tool = ['name' => 'ping', 'description' => 'Ping.', 'schema' => ['type' => 'object', 'properties' => [], 'additionalProperties' => false]];

        (new AnthropicGateway('k', $transport, 30, 0))->send($this->request(['tools' => [$tool]]));

        $this->assertStringContainsString('"properties":{}', $transport->bodies[0]);
    }

    public function test_the_answer_is_kept_as_the_api_returned_it_and_the_usage_is_read(): void
    {
        $content = [
            ['type' => 'thinking', 'thinking' => 'hmm', 'signature' => 'SIG=='],
            ['type' => 'text', 'text' => 'Je cherche.'],
            ['type' => 'tool_use', 'id' => 'toolu_1', 'name' => 'crm_search', 'input' => ['q' => 'x'], 'caller' => ['type' => 'direct']],
        ];
        $transport = $this->transport(200, $this->message($content, 'tool_use'));

        $response = (new AnthropicGateway('k', $transport, 30, 0))->send($this->request());

        $this->assertSame('tool_use', $response->stopReason);
        $this->assertSame('SIG==', $response->content[0]['signature']);
        $this->assertSame([['id' => 'toolu_1', 'name' => 'crm_search', 'input' => ['q' => 'x']]], $response->toolUses());
        $this->assertSame('Je cherche.', $response->text());
        $this->assertSame(10, $response->inputTokens);
        $this->assertSame(5, $response->outputTokens);
        $this->assertSame(7, $response->cacheReadTokens);
        $this->assertSame(3, $response->cacheWriteTokens);
    }

    public function test_a_stored_history_goes_back_unchanged_and_an_empty_tool_input_stays_an_object(): void
    {
        $transport = $this->transport(200, $this->message([['type' => 'text', 'text' => 'ok']]));
        $history = [
            ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'Bonjour']]],
            ['role' => 'assistant', 'content' => [
                ['type' => 'thinking', 'thinking' => 'hmm', 'signature' => 'SIG=='],
                ['type' => 'tool_use', 'id' => 'toolu_1', 'name' => 'ping', 'input' => [], 'caller' => ['type' => 'direct']],
            ]],
            ['role' => 'user', 'content' => [['type' => 'tool_result', 'tool_use_id' => 'toolu_1', 'content' => 'pong']]],
        ];

        (new AnthropicGateway('k', $transport, 30, 0))->send($this->request(['messages' => $history]));

        $sent = json_decode($transport->bodies[0], true)['messages'];

        $this->assertSame('SIG==', $sent[1]['content'][0]['signature']);
        $this->assertSame('toolu_1', $sent[2]['content'][0]['tool_use_id']);
        $this->assertStringContainsString('"input":{}', $transport->bodies[0]);
    }

    public function test_a_refusal_carries_its_category(): void
    {
        $transport = $this->transport(200, $this->message([], 'refusal', ['stop_details' => ['type' => 'refusal', 'category' => 'cyber', 'explanation' => 'x']]));

        $response = (new AnthropicGateway('k', $transport, 30, 0))->send($this->request());

        $this->assertSame('refusal', $response->stopReason);
        $this->assertSame('cyber', $response->refusalCategory);
    }

    /**
     * @dataProvider failures
     */
    public function test_the_errors_of_the_api_become_readable_exceptions(int $status, string $type, string $reason, bool $retryable): void
    {
        $transport = $this->transport($status, ['type' => 'error', 'error' => ['type' => $type, 'message' => 'detail interne avec sk-ant-secret']]);

        try {
            (new AnthropicGateway('sk-ant-secret', $transport, 30, 0))->send($this->request());
            $this->fail('An exception was expected.');
        } catch (ClaudeException $e) {
            $this->assertSame($reason, $e->reason);
            $this->assertSame($retryable, $e->retryable);
            // The message shown to the user never repeats what the API said
            $this->assertStringNotContainsString('sk-ant-secret', $e->getMessage());
            $this->assertStringNotContainsString('detail interne', $e->getMessage());
        }
    }

    public static function failures(): array
    {
        return [
            'invalid key' => [401, 'authentication_error', ClaudeException::INVALID_KEY, false],
            'forbidden' => [403, 'permission_error', ClaudeException::INVALID_KEY, false],
            'rate limit' => [429, 'rate_limit_error', ClaudeException::RATE_LIMITED, true],
            'bad request' => [400, 'invalid_request_error', ClaudeException::BAD_REQUEST, false],
            'server error' => [500, 'api_error', ClaudeException::OVERLOADED, true],
            'overloaded' => [529, 'overloaded_error', ClaudeException::OVERLOADED, true],
        ];
    }
}
