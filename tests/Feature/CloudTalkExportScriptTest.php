<?php

namespace Tests\Feature;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\ProjectUserAccessCheck;
use App\Http\Middleware\UserLastProjectCheck;
use App\Http\Middleware\UserTrackActivity;
use App\Models\User;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CloudTalkExportScriptTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'cloudtalk_export_test',
            'database.connections.cloudtalk_export_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
        Event::fake([Authenticated::class]);
        Http::preventStrayRequests();
        $this->withoutMiddleware([
            Authenticate::class, ProjectUserAccessCheck::class, UserLastProjectCheck::class, UserTrackActivity::class,
        ]);
        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('role')->nullable();
        });
        $schema->create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->unsignedBigInteger('creator_id');
            $table->softDeletes();
        });
        $schema->create('user_project', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('project_id');
        });
        $schema->create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
        });
        $schema->create('roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('name');
            $table->string('guard_name');
        });
        $schema->create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
        });
        $schema->create('lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('operator');
            $table->json('config');
        });
        DB::table('users')->insert([
            ['id' => 1, 'role' => 'super_admin'], ['id' => 2, 'role' => null], ['id' => 3, 'role' => null],
        ]);
        DB::table('projects')->insert([
            ['id' => 1, 'slug' => 'test', 'creator_id' => 3], ['id' => 2, 'slug' => 'other', 'creator_id' => 1],
        ]);
        DB::table('user_project')->insert([
            ['user_id' => 2, 'project_id' => 1], ['user_id' => 3, 'project_id' => 1],
        ]);
        $config = json_encode(['api_key_id' => 'fixture-key', 'api_key_secret' => 'fixture-secret', 'numero' => '+33123456789']);
        DB::table('lines')->insert([
            ['id' => 1, 'project_id' => 1, 'user_id' => 1, 'name' => 'CloudTalk', 'operator' => 'cloudtalk', 'config' => $config],
            ['id' => 2, 'project_id' => 2, 'user_id' => 1, 'name' => 'Other', 'operator' => 'cloudtalk', 'config' => $config],
            ['id' => 3, 'project_id' => 1, 'user_id' => 1, 'name' => 'Twilio', 'operator' => 'twilio', 'config' => $config],
            ['id' => 4, 'project_id' => 1, 'user_id' => 1, 'name' => 'Incomplete', 'operator' => 'cloudtalk', 'config' => '{}'],
        ]);
        $this->actingAs(User::findOrFail(1));
    }

    protected function tearDown(): void
    {
        DB::purge('cloudtalk_export_test');
        parent::tearDown();
    }

    public function test_configuration_options_expose_only_public_metadata_for_this_project(): void
    {
        $response = $this->getJson($this->endpoint('export-configurations'))->assertOk()
            ->assertJsonCount(2, 'configurations')->assertJsonPath('configurations.0.id', 1)
            ->assertJsonPath('configurations.0.numero', '+33123456789');
        $this->assertStringNotContainsString('fixture-key', $response->getContent());
        $this->assertStringNotContainsString('fixture-secret', $response->getContent());
        Http::assertNothingSent();
    }

    public function test_selected_line_credentials_are_available_only_through_the_private_endpoint(): void
    {
        $response = $this->getJson($this->endpoint('export-credentials/1'))->assertOk()
            ->assertExactJson(['api_key_id' => 'fixture-key', 'api_key_secret' => 'fixture-secret']);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->getJson($this->endpoint('export-credentials/4'))->assertOk()
            ->assertExactJson(['api_key_id' => '', 'api_key_secret' => '']);
        foreach ([2, 3, 999] as $line) {
            $this->getJson($this->endpoint('export-credentials/' . $line))->assertNotFound();
        }
        Http::assertNothingSent();
    }

    public function test_edited_credentials_are_used_for_the_script_without_updating_the_line(): void
    {
        $response = $this->postJson($this->endpoint(), $this->payload([
            'api_key_id' => 'edited-key', 'api_key_secret' => 'edited-secret',
        ]))->assertOk();
        $config = $this->decodeConfig($response->getContent());
        $this->assertSame('edited-key', $config['api_key_id']);
        $this->assertSame('edited-secret', $config['api_key_secret']);
        $stored = json_decode(DB::table('lines')->where('id', 1)->value('config'), true);
        $this->assertSame('fixture-key', $stored['api_key_id']);
        $this->assertSame('fixture-secret', $stored['api_key_secret']);
        Http::assertNothingSent();
    }

    public function test_manual_credentials_allow_an_export_without_any_cloudtalk_line(): void
    {
        DB::table('lines')->where('project_id', 1)->delete();
        $this->getJson($this->endpoint('export-configurations'))->assertOk()->assertJsonCount(0, 'configurations');
        $response = $this->postJson($this->endpoint(), $this->payload([
            'line_id' => null, 'api_key_id' => 'manual-key', 'api_key_secret' => 'manual-secret',
        ]))->assertOk();
        $config = $this->decodeConfig($response->getContent());
        $this->assertSame('manual-key', $config['api_key_id']);
        $this->assertSame('manual-secret', $config['api_key_secret']);
        Http::assertNothingSent();
    }

    public function test_generated_download_contains_dates_and_credentials_without_contacting_cloudtalk(): void
    {
        $response = $this->postJson($this->endpoint(), $this->payload())->assertOk()
            ->assertHeader('Content-Type', 'application/octet-stream');
        $script = $response->getContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $script);
        $this->assertStringContainsString('cloudtalk-export-2026-03-01-2026-03-01.ps1', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringNotContainsString('__CLOUDTALK_EXPORT_CONFIG_BASE64__', $script);
        $config = $this->decodeConfig($script);
        $this->assertSame('fixture-key', $config['api_key_id']);
        $this->assertSame('fixture-secret', $config['api_key_secret']);
        $this->assertSame('2026-03-01 00:00:00', $config['date_from']);
        $this->assertSame('2026-03-01 23:59:59', $config['date_to']);
        $this->assertFalse($config['download_audio']);
        Http::assertNothingSent();
    }

    public function test_dates_include_the_whole_local_day_across_a_daylight_saving_change(): void
    {
        $response = $this->postJson($this->endpoint(), $this->payload([
            'date_from' => '2026-03-29', 'date_to' => '2026-03-29', 'timezone' => 'Europe/Paris', 'download_audio' => true,
        ]))->assertOk();
        $config = $this->decodeConfig($response->getContent());
        $this->assertSame('2026-03-28 23:00:00', $config['date_from']);
        $this->assertSame('2026-03-29 21:59:59', $config['date_to']);
        $this->assertTrue($config['download_audio']);
    }

    public function test_madagascar_dates_are_converted_to_utc_before_generating_the_script(): void
    {
        $response = $this->postJson($this->endpoint(), $this->payload([
            'date_from' => '2026-10-23', 'date_to' => '2026-10-23', 'timezone' => 'Indian/Antananarivo',
        ]))->assertOk();
        $config = $this->decodeConfig($response->getContent());
        $this->assertSame('2026-10-22 21:00:00', $config['date_from']);
        $this->assertSame('2026-10-23 20:59:59', $config['date_to']);
        $this->assertSame('Indian/Antananarivo', $config['timezone']);
    }

    public function test_special_characters_in_credentials_stay_data_instead_of_powershell_code(): void
    {
        $secret = "quote'\"; \$(throw 'injected')\nclé secrète";
        DB::table('lines')->where('id', 1)->update(['config' => json_encode(['api_key_id' => 'key', 'api_key_secret' => $secret])]);
        $response = $this->postJson($this->endpoint(), $this->payload(['api_key_id' => 'key', 'api_key_secret' => $secret]))->assertOk();
        $this->assertSame($secret, $this->decodeConfig($response->getContent())['api_key_secret']);
        $this->assertStringNotContainsString("throw 'injected'", $response->getContent());
    }

    public function test_project_admin_can_list_credentials_and_download_the_script(): void
    {
        $this->actingAs(User::findOrFail(3));
        $this->getJson($this->endpoint('export-configurations'))->assertOk()
            ->assertJsonCount(2, 'configurations')->assertJsonPath('configurations.0.id', 1);
        $this->getJson($this->endpoint('export-credentials/1'))->assertOk()
            ->assertExactJson(['api_key_id' => 'fixture-key', 'api_key_secret' => 'fixture-secret']);
        $response = $this->postJson($this->endpoint(), $this->payload())->assertOk();
        $config = $this->decodeConfig($response->getContent());
        $this->assertSame('fixture-key', $config['api_key_id']);
        $this->assertSame('fixture-secret', $config['api_key_secret']);
        Http::assertNothingSent();
    }

    public function test_ordinary_project_member_cannot_access_export_endpoints(): void
    {
        $this->actingAs(User::findOrFail(2));
        $this->getJson($this->endpoint('export-configurations'))->assertForbidden();
        $this->getJson($this->endpoint('export-credentials/1'))->assertForbidden();
        $this->postJson($this->endpoint(), $this->payload())->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_ordinary_member_cannot_access_exports_in_another_project(): void
    {
        $this->actingAs(User::findOrFail(2));
        $endpoint = '/api/project/other/line/cloudtalk/';
        $this->getJson($endpoint . 'export-configurations')->assertNotFound();
        $this->getJson($endpoint . 'export-credentials/2')->assertNotFound();
        $this->postJson($endpoint . 'export-script', $this->payload(['line_id' => 2]))->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_guest_cannot_access_export_endpoints(): void
    {
        $this->withMiddleware(Authenticate::class);
        $this->app['auth']->forgetGuards();
        $this->getJson($this->endpoint('export-configurations'))->assertUnauthorized();
        $this->getJson($this->endpoint('export-credentials/1'))->assertUnauthorized();
        $this->postJson($this->endpoint(), $this->payload())->assertUnauthorized();
        Http::assertNothingSent();
    }

    public function test_a_configuration_from_another_project_or_operator_cannot_be_exported(): void
    {
        foreach ([0, 2, 3, 999] as $line) {
            $this->postJson($this->endpoint(), $this->payload(['line_id' => $line]))->assertNotFound();
        }
        $response = $this->postJson($this->endpoint(), $this->payload(['line_id' => 4]))->assertOk();
        $this->assertSame('fixture-secret', $this->decodeConfig($response->getContent())['api_key_secret']);
    }

    public function test_invalid_dates_timezones_and_options_are_rejected(): void
    {
        foreach ([
            ['date_from' => '2026-02-30'], ['date_from' => '2026-03-02'], ['date_to' => '2026-02-28'],
            ['timezone' => 'invalid'], ['download_audio' => 'yes'], ['download_audio' => null],
            ['api_key_id' => null], ['api_key_secret' => ''], ['api_key_secret' => ['array']],
        ] as $invalid) {
            $this->postJson($this->endpoint(), $this->payload($invalid))->assertUnprocessable();
        }
        Http::assertNothingSent();
    }

    protected function payload(array $overrides = []): array
    {
        return array_replace([
            'line_id' => 1, 'date_from' => '2026-03-01', 'date_to' => '2026-03-01',
            'api_key_id' => 'fixture-key', 'api_key_secret' => 'fixture-secret',
            'timezone' => 'UTC', 'download_audio' => false,
        ], $overrides);
    }

    protected function endpoint(string $action = 'export-script'): string
    {
        return '/api/project/test/line/cloudtalk/' . $action;
    }

    protected function decodeConfig(string $script): array
    {
        $this->assertSame(1, preg_match("/FromBase64String\\('([A-Za-z0-9+\/=]+)'\\)/", $script, $matches));
        return json_decode(base64_decode($matches[1], true), true, 512, JSON_THROW_ON_ERROR);
    }
}
