<?php

namespace Tests\Feature;

use App\Http\Middleware\UserTrackActivity;
use App\Models\User;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClaudeSettingsTest extends TestCase
{
    protected const ENDPOINT = '/api/settings/claude';

    protected function setUp(): void
    {
        parent::setUp();

        // Never run application migrations or touch the configured project database.
        config([
            'database.default' => 'claude_settings_test',
            'database.connections.claude_settings_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
            'cache.default' => 'array',
        ]);
        Event::fake([Authenticated::class]);
        Http::preventStrayRequests();
        $this->fakeModels(Http::response([
            'data' => [['id' => 'claude-test-model', 'type' => 'model']],
        ]));
        $this->withoutMiddleware(UserTrackActivity::class);

        DB::connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->string('two_factors_secret')->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_10_27_000001_add_claude_api_key_to_users_table.php'))->up();

        DB::table('users')->insert([
            ['id' => 1, 'name' => 'First user', 'email' => 'first@example.test', 'role' => null, 'ip_address' => '127.0.0.1'],
            ['id' => 2, 'name' => 'Second user', 'email' => 'second@example.test', 'role' => null, 'ip_address' => '127.0.0.1'],
        ]);
    }

    protected function tearDown(): void
    {
        DB::purge('claude_settings_test');
        parent::tearDown();
    }

    public function test_guests_cannot_read_or_change_claude_settings(): void
    {
        $this->getJson(self::ENDPOINT)->assertUnauthorized();
        $this->putJson(self::ENDPOINT, ['api_key' => 'guest-key'])->assertUnauthorized();

        $this->assertSame(0, DB::table('users')->whereNotNull('claude_api_key')->count());
        Http::assertNothingSent();
    }

    public function test_ordinary_user_without_a_project_can_save_and_replace_an_encrypted_key(): void
    {
        $this->signIn(1);
        $this->getJson(self::ENDPOINT)->assertOk()->assertExactJson(['configured' => false, 'valid' => null, 'status' => 'not_configured']);

        $response = $this->putJson(self::ENDPOINT, ['api_key' => '  first-private-key  '])
            ->assertOk()->assertExactJson(['configured' => true, 'valid' => true, 'status' => 'valid']);
        $this->assertStringNotContainsString('first-private-key', $response->getContent());
        $stored = DB::table('users')->where('id', 1)->value('claude_api_key');
        $this->assertIsString($stored);
        $this->assertNotSame('first-private-key', $stored);
        $this->assertStringNotContainsString('first-private-key', $stored);
        $this->assertSame('first-private-key', User::findOrFail(1)->claude_api_key);
        $this->assertNull(DB::table('users')->where('id', 2)->value('claude_api_key'));

        $this->putJson(self::ENDPOINT, ['api_key' => 'replacement-private-key'])
            ->assertOk()->assertExactJson(['configured' => true, 'valid' => true, 'status' => 'valid']);
        $this->assertSame('replacement-private-key', User::findOrFail(1)->claude_api_key);
        $this->assertNotSame($stored, DB::table('users')->where('id', 1)->value('claude_api_key'));
        Http::assertSentCount(2);
    }

    public function test_each_user_has_a_separate_key_even_when_another_user_or_project_is_requested(): void
    {
        $this->signIn(1);
        $this->putJson(self::ENDPOINT, ['api_key' => 'first-user-secret'])->assertOk();

        $this->signIn(2);
        $this->getJson(self::ENDPOINT . '?user_id=1&project_id=100')
            ->assertOk()->assertExactJson(['configured' => false, 'valid' => null, 'status' => 'not_configured']);
        $this->putJson(self::ENDPOINT, [
            'api_key' => 'second-user-secret', 'user_id' => 1, 'project_id' => 100,
        ])->assertOk()->assertExactJson(['configured' => true, 'valid' => true, 'status' => 'valid']);

        $this->assertSame('first-user-secret', User::findOrFail(1)->claude_api_key);
        $this->assertSame('second-user-secret', User::findOrFail(2)->claude_api_key);
        $this->getJson(self::ENDPOINT)->assertOk()->assertExactJson(['configured' => true, 'valid' => true, 'status' => 'valid']);
    }

    public function test_existing_key_is_hidden_from_settings_auth_and_user_serialization(): void
    {
        $this->signIn(1);
        $this->putJson(self::ENDPOINT, ['api_key' => 'secret-never-returned'])->assertOk();
        $ciphertext = DB::table('users')->where('id', 1)->value('claude_api_key');

        $response = $this->getJson(self::ENDPOINT)->assertOk()->assertExactJson(['configured' => true, 'valid' => true, 'status' => 'valid']);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringNotContainsString('secret-never-returned', $response->getContent());
        $this->assertStringNotContainsString($ciphertext, $response->getContent());

        $user = User::findOrFail(1);
        $this->assertArrayNotHasKey('claude_api_key', $user->toArray());
        $this->assertStringNotContainsString('secret-never-returned', $user->toJson());
        $this->signIn(1);
        $auth = $this->getJson('/api/auth')->assertOk()->assertJsonMissingPath('claude_api_key');
        $this->assertStringNotContainsString('secret-never-returned', $auth->getContent());
        $this->assertStringNotContainsString($ciphertext, $auth->getContent());
    }

    public function test_personal_key_remains_accessible_in_an_impersonated_project_session(): void
    {
        $this->signIn(2);
        $this->withSession(['impersonate_project' => 'example']);

        $this->getJson(self::ENDPOINT)->assertOk()->assertExactJson(['configured' => false, 'valid' => null, 'status' => 'not_configured']);
        $this->putJson(self::ENDPOINT, ['api_key' => 'current-impersonated-user-key'])
            ->assertOk()->assertExactJson(['configured' => true, 'valid' => true, 'status' => 'valid']);

        $this->assertSame('current-impersonated-user-key', User::findOrFail(2)->claude_api_key);
        $this->assertNull(DB::table('users')->where('id', 1)->value('claude_api_key'));
    }

    /** @dataProvider invalidKeys */
    public function test_invalid_input_does_not_replace_an_existing_key(array $input): void
    {
        $this->signIn(1);
        $this->putJson(self::ENDPOINT, ['api_key' => 'existing-private-key'])->assertOk();
        $before = DB::table('users')->where('id', 1)->value('claude_api_key');

        $this->putJson(self::ENDPOINT, $input)->assertUnprocessable()->assertJsonValidationErrors('api_key');

        $this->assertSame($before, DB::table('users')->where('id', 1)->value('claude_api_key'));
        $this->assertSame('existing-private-key', User::findOrFail(1)->claude_api_key);
    }

    public static function invalidKeys(): array
    {
        return [
            'missing' => [[]],
            'empty' => [['api_key' => '']],
            'whitespace' => [['api_key' => '   ']],
            'null' => [['api_key' => null]],
            'array' => [['api_key' => ['unexpected']]],
            'too long' => [['api_key' => str_repeat('x', 4097)]],
        ];
    }

    public function test_opening_the_modal_checks_only_the_current_users_stored_key(): void
    {
        $this->signIn(1);
        $this->getJson(self::ENDPOINT)->assertOk();
        Http::assertNothingSent();

        $this->putJson(self::ENDPOINT, ['api_key' => 'current-user-key'])->assertOk();
        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && $request->url() === 'https://api.anthropic.com/v1/models?limit=1'
            && $request->hasHeader('x-api-key', 'current-user-key')
            && $request->hasHeader('anthropic-version', '2023-06-01'));

        $this->fakeModels(Http::response(['error' => ['type' => 'authentication_error']], 401));
        $this->getJson(self::ENDPOINT)->assertOk()->assertExactJson([
            'configured' => true, 'valid' => false, 'status' => 'invalid',
        ]);
        $this->assertSame('current-user-key', User::findOrFail(1)->claude_api_key);

        $this->signIn(2);
        $this->getJson(self::ENDPOINT)->assertOk()->assertExactJson([
            'configured' => false, 'valid' => null, 'status' => 'not_configured',
        ]);
    }

    /** @dataProvider apiFailures */
    public function test_failed_api_check_preserves_the_saved_key_and_reports_its_actual_state(
        int $providerStatus, string $status, ?bool $valid, int $saveStatus
    ): void {
        $this->signIn(1);
        $this->putJson(self::ENDPOINT, ['api_key' => 'old-key'])->assertOk();
        $before = DB::table('users')->where('id', 1)->value('claude_api_key');

        $this->fakeModels(Http::response(['error' => ['message' => 'Provider response']], $providerStatus));
        $this->putJson(self::ENDPOINT, ['api_key' => 'new-key'])->assertStatus($saveStatus)
            ->assertExactJson(['configured' => true, 'valid' => $valid, 'status' => $status]);
        $this->assertSame($before, DB::table('users')->where('id', 1)->value('claude_api_key'));
        $this->getJson(self::ENDPOINT)->assertOk()
            ->assertExactJson(['configured' => true, 'valid' => $valid, 'status' => $status]);
    }

    public static function apiFailures(): array
    {
        return [
            'invalid credentials' => [401, 'invalid', false, 422],
            'access refused' => [403, 'forbidden', false, 422],
            'rate limited' => [429, 'rate_limited', null, 503],
            'account configuration' => [400, 'configuration_error', null, 503],
            'provider failure' => [500, 'verification_unavailable', null, 503],
            'unexpected redirect' => [302, 'verification_unavailable', null, 503],
        ];
    }

    public function test_no_accessible_models_prevents_saving_and_malformed_success_is_not_accepted(): void
    {
        $this->signIn(1);
        $this->fakeModels(Http::response(['data' => []]));
        $this->putJson(self::ENDPOINT, ['api_key' => 'no-models-key'])->assertUnprocessable()
            ->assertExactJson(['configured' => false, 'valid' => false, 'status' => 'no_models']);
        $this->assertNull(DB::table('users')->where('id', 1)->value('claude_api_key'));

        $this->fakeModels(Http::response('<html>Proxy error</html>', 200));
        $this->putJson(self::ENDPOINT, ['api_key' => 'unverified-key'])->assertStatus(503)
            ->assertJson(['valid' => null, 'status' => 'verification_unavailable']);
        $this->assertNull(DB::table('users')->where('id', 1)->value('claude_api_key'));
    }

    public function test_connection_failure_is_not_reported_as_an_invalid_key(): void
    {
        $this->signIn(1);
        $this->putJson(self::ENDPOINT, ['api_key' => 'old-key'])->assertOk();
        $before = DB::table('users')->where('id', 1)->value('claude_api_key');
        $this->fakeModels(fn () => throw new ConnectionException('Connection failed'));

        $this->getJson(self::ENDPOINT)->assertOk()->assertExactJson([
            'configured' => true, 'valid' => null, 'status' => 'verification_unavailable',
        ]);
        $this->putJson(self::ENDPOINT, ['api_key' => 'new-key'])->assertStatus(503)
            ->assertJson(['valid' => null, 'status' => 'verification_unavailable']);
        $this->assertSame($before, DB::table('users')->where('id', 1)->value('claude_api_key'));
    }

    public function test_a_rejected_first_key_is_not_saved_and_header_injection_is_not_sent(): void
    {
        $this->signIn(1);
        $this->putJson(self::ENDPOINT, ['api_key' => "bad\r\nheader"])->assertUnprocessable()
            ->assertJson(['configured' => false, 'valid' => false, 'status' => 'invalid']);
        Http::assertNothingSent();

        $this->fakeModels(Http::response([], 401));
        $this->putJson(self::ENDPOINT, ['api_key' => 'invalid-key'])->assertUnprocessable()
            ->assertJson(['configured' => false, 'valid' => false, 'status' => 'invalid']);
        $this->assertNull(DB::table('users')->where('id', 1)->value('claude_api_key'));
    }

    protected function fakeModels($response): void
    {
        // Replace prior stubs so each failure scenario actually reaches the validator.
        Http::swap(new Factory());
        Http::preventStrayRequests();
        Http::fake(['api.anthropic.com/v1/models*' => $response]);
    }

    protected function signIn(int $userId): void
    {
        $this->actingAs(User::findOrFail($userId), 'sanctum');
    }
}
