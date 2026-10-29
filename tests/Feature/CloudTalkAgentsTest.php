<?php

namespace Tests\Feature;

use App\Services\CloudTalk;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class CloudTalkAgentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    public function test_agents_expose_the_provider_default_number_and_keep_identity_and_authenticated_pagination(): void
    {
        $this->fakeAgents([['Agent' => [
            'id' => 7,
            'firstname' => ' Alice ',
            'lastname' => ' Martin ',
            'email' => ' alice@example.com ',
            'extension' => ' 201 ',
            'default_number' => '+33 (1) 23-45.67.89',
        ]]]);

        $agents = (new CloudTalk())->agents($this->credentials(), 25, 2);

        $this->assertSame([[
            'id' => '7',
            'firstname' => 'Alice',
            'lastname' => 'Martin',
            'name' => 'Alice Martin',
            'email' => 'alice@example.com',
            'extension' => '201',
            'default_number' => '+33123456789',
            'label' => 'Alice Martin (#201)',
        ]], $agents);
        Http::assertSent(fn (Request $request) => $request->method() === 'GET'
            && str_starts_with($request->url(), 'https://my.cloudtalk.io/api/agents/index.json?')
            && $request['limit'] == 25 && $request['page'] == 2
            && $request->hasHeader('Accept', 'application/json')
            && $request->hasHeader('Authorization', 'Basic ' . base64_encode('fixture-key:fixture-secret')));
        Http::assertSentCount(1);
    }

    public function test_provider_default_numbers_accept_international_formats_and_reject_malformed_values(): void
    {
        $cases = [
            ['+12025550178', '+12025550178'],
            ['33123456789', '+33123456789'],
            [12025550178, '+12025550178'],
            ['00 33 (1) 23-45.67.89', '+33123456789'],
            ['+123456789012345', '+123456789012345'],
            [null, null],
            ['', null],
            ['   ', null],
            [true, null],
            [false, null],
            [12025550178.5, null],
            [0, null],
            [-12025550178, null],
            [['number' => '+12025550178'], null],
            [(object) ['number' => '+12025550178'], null],
            ['not-a-number', null],
            ['+0123456789', null],
            ['+1', null],
            ['+1234567890123456', null],
            ['+33 1 23 45 67 89 ext 2', null],
            ['++33123456789', null],
        ];
        $this->fakeAgents(array_map(fn ($case, $index) => [
            'id' => $index + 1,
            'firstname' => 'Agent',
            'default_number' => $case[0],
        ], $cases, array_keys($cases)));

        $agents = (new CloudTalk())->agents($this->credentials());

        $this->assertSame(array_column($cases, 1), array_column($agents, 'default_number'));
        foreach ($agents as $agent) {
            $this->assertArrayNotHasKey('sms_capable', $agent);
        }
    }

    public function test_missing_default_number_never_falls_back_to_extension_or_an_arbitrary_associated_number(): void
    {
        $this->fakeAgents([
            ['Agent' => [
                'id' => 1,
                'extension' => '12025550178',
                'call_number_id' => 12,
                'associated_numbers' => ['+12025550143'],
            ]],
            ['Agent' => ['id' => 2, 'firstname' => 'Bob', 'default_number' => false]],
            ['Agent' => ['id' => null, 'default_number' => '+12025550178']],
        ]);

        $agents = (new CloudTalk())->agents($this->credentials());

        $this->assertCount(2, $agents);
        $this->assertNull($agents[0]['default_number']);
        $this->assertSame('12025550178', $agents[0]['extension']);
        $this->assertNull($agents[1]['default_number']);
        $this->assertSame('Bob', $agents[1]['label']);
    }

    public function test_rejected_credentials_still_fail_without_any_sms_request(): void
    {
        Http::fake(['my.cloudtalk.io/api/agents/index.json*' => Http::response([
            'responseData' => ['status' => 401, 'message' => 'Unauthorized'],
        ], 401)]);

        try {
            (new CloudTalk())->agents($this->credentials());
            $this->fail('Invalid CloudTalk credentials should be rejected.');
        } catch (RuntimeException $exception) {
            $this->assertSame(401, $exception->getCode());
            $this->assertSame('Identifiants API CloudTalk invalides.', $exception->getMessage());
        }

        Http::assertSentCount(1);
        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), '/sms/'));
    }

    protected function credentials(): array
    {
        return ['api_key_id' => 'fixture-key', 'api_key_secret' => 'fixture-secret'];
    }

    protected function fakeAgents(array $agents): void
    {
        Http::fake(['my.cloudtalk.io/api/agents/index.json*' => Http::response([
            'responseData' => ['status' => 200, 'data' => $agents],
        ])]);
    }
}
