<?php

namespace Tests\Feature;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\UserTrackActivity;
use App\Models\Line;
use App\Models\Project;
use App\Services\CloudTalk;
use App\Services\DashboardRecordings;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class DashboardRecordingsTest extends TestCase
{
    protected const FROM = '2026-10-01 00:00:00';

    protected const TO = '2026-10-31 23:59:59';

    protected function setUp(): void
    {
        parent::setUp();

        // Never migrate or write to the application's configured project database.
        config([
            'database.default' => 'dashboard_recordings_test',
            'database.connections.dashboard_recordings_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
            'cache.default' => 'array',
        ]);
        Cache::flush();
        Storage::fake('interactions');
        Http::preventStrayRequests();
        // Keep route-model binding active while isolating authentication and tracking.
        $this->withoutMiddleware([Authenticate::class, UserTrackActivity::class]);

        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->softDeletes();
        });
        $schema->create('users', function (Blueprint $table) {
            $table->id();
        });
        $schema->create('user_project', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
        });
        $schema->create('prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->softDeletes();
        });
        $schema->create('lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
            $table->string('operator');
            $table->json('config');
        });
        $schema->create('interactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->string('source');
            $table->string('status')->nullable();
            $table->string('path')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        DB::table('projects')->insert([
            ['id' => 1, 'slug' => 'recordings-test'],
            ['id' => 2, 'slug' => 'other-project'],
        ]);
        DB::table('users')->insert([['id' => 1], ['id' => 2], ['id' => 3]]);
        DB::table('user_project')->insert([
            ['project_id' => 1, 'user_id' => 1],
            ['project_id' => 1, 'user_id' => 2],
            ['project_id' => 2, 'user_id' => 3],
        ]);
        DB::table('prospects')->insert([
            ['id' => 1, 'project_id' => 1, 'deleted_at' => null],
            ['id' => 2, 'project_id' => 2, 'deleted_at' => null],
            ['id' => 3, 'project_id' => 1, 'deleted_at' => '2026-10-01 00:00:00'],
        ]);
        $this->line(1, 1);
    }

    protected function tearDown(): void
    {
        DB::purge('dashboard_recordings_test');
        parent::tearDown();
    }

    public function test_unrecorded_and_invalid_cloudtalk_calls_do_not_produce_an_audio_url(): void
    {
        $this->interaction('100', [], ['call_id' => '100', 'recorded' => false]);
        $this->interaction('not-a-numeric-call-id');
        $this->interaction('102', [], ['call_id' => '102']);
        $cloudTalk = Mockery::mock(CloudTalk::class);
        $cloudTalk->shouldNotReceive('recordingMedia');

        $this->assertSame([], $this->service()->urls([1], self::FROM, self::TO));
        $this->assertNull($this->service()->media(1, self::FROM, self::TO, $cloudTalk));
        Http::assertNothingSent();
    }

    public function test_completed_event_does_not_offer_audio_until_recording_ready_is_promoted(): void
    {
        $data = [
            'call_id' => '100',
            'cloudtalk_ended' => ['data' => ['recorded' => true]],
        ];
        $id = $this->interaction('100', [], $data);
        $cloudTalk = Mockery::mock(CloudTalk::class);
        $cloudTalk->shouldNotReceive('recordingMedia');

        $this->assertSame([], $this->service()->urls([1], self::FROM, self::TO));
        $this->assertNull($this->service()->media(1, self::FROM, self::TO, $cloudTalk));

        // Match the promotion performed by the call.recording_ready webhook.
        $data['recorded'] = true;
        $data['cloudtalk_recording'] = ['type' => 'call.recording_ready'];
        DB::table('interactions')->where('id', $id)->update(['data' => json_encode($data)]);

        $urls = $this->service()->urls([1], self::FROM, self::TO);

        $this->assertArrayHasKey(1, $urls);
        $this->assertStringContainsString('/project/recordings-test/', $urls[1]);
        Http::assertNothingSent();
    }

    public function test_latest_recording_uses_call_date_rather_than_import_id_and_skips_newer_unrecorded_calls(): void
    {
        $this->interaction('100', ['started_at' => '2026-10-20 08:00:00']);
        $this->interaction('101', ['started_at' => '2026-10-10 08:00:00']); // Imported later.
        $this->interaction('102', ['started_at' => '2026-10-21 08:00:00'], [
            'call_id' => '102', 'recorded' => false,
        ]);
        $cloudTalk = $this->mediaMock('100', 'newest-recording');

        $this->assertSame('newest-recording', $this->service()->media(1, self::FROM, self::TO, $cloudTalk)['body']);
    }

    public function test_equal_call_dates_use_the_latest_id_and_missing_start_uses_creation_date(): void
    {
        $this->interaction('100', ['started_at' => '2026-10-20 08:00:00']);
        $this->interaction('101', ['started_at' => null, 'created_at' => '2026-10-20 08:00:00']);
        $cloudTalk = $this->mediaMock('101', 'last-id');

        $this->assertSame('last-id', $this->service()->media(1, self::FROM, self::TO, $cloudTalk)['body']);
    }

    public function test_recordings_are_scoped_by_project_user_period_and_non_deleted_prospect(): void
    {
        $this->interaction('100');
        $this->interaction('101', ['prospect_id' => 2, 'started_at' => '2026-10-30 08:00:00']);
        $this->interaction('102', ['creator_id' => 2, 'started_at' => '2026-10-30 08:00:00']);
        $this->interaction('103', ['prospect_id' => 3, 'started_at' => '2026-10-30 08:00:00']);
        $this->interaction('104', ['started_at' => '2026-11-01 00:00:00']);
        $cloudTalk = $this->mediaMock('100', 'scoped-recording');

        $urls = $this->service()->urls([1], self::FROM, self::TO);
        $this->assertSame([1], array_keys($urls));
        $this->assertSame('scoped-recording', $this->service()->media(1, self::FROM, self::TO, $cloudTalk)['body']);
    }

    public function test_cloudtalk_recordings_need_the_owners_line_with_credentials(): void
    {
        $this->interaction('100', ['creator_id' => 2]);
        $this->line(2, 2); // Another project's line is insufficient.
        $cloudTalk = Mockery::mock(CloudTalk::class);
        $cloudTalk->shouldNotReceive('recordingMedia');

        $this->assertSame([], $this->service()->urls([2], self::FROM, self::TO));
        $this->assertNull($this->service()->media(2, self::FROM, self::TO, $cloudTalk));

        DB::table('lines')->where('project_id', 1)->where('user_id', 1)
            ->update(['config' => json_encode(['agent_id' => '42'])]);
        $this->interaction('101');
        $this->assertSame([], $this->service()->urls([1], self::FROM, self::TO));
    }

    public function test_local_recordings_require_an_existing_file_and_skip_a_missing_newer_file(): void
    {
        Storage::disk('interactions')->put('recordings-test/call.wav', 'local-audio');
        $this->interaction('100', [
            'source' => 'telephone', 'path' => 'recordings-test/call.wav',
        ]);
        $this->interaction('101', [
            'source' => 'telephone', 'path' => 'recordings-test/missing.wav',
            'started_at' => '2026-10-21 08:00:00',
        ]);
        $cloudTalk = Mockery::mock(CloudTalk::class);
        $cloudTalk->shouldNotReceive('recordingMedia');

        $this->assertArrayHasKey(1, $this->service()->urls([1], self::FROM, self::TO));
        $this->assertSame('local-audio', $this->service()->media(1, self::FROM, self::TO, $cloudTalk)['body']);

        Storage::disk('interactions')->delete('recordings-test/call.wav');
        $this->assertSame([], $this->service()->urls([1], self::FROM, self::TO));
        $this->assertNull($this->service()->media(1, self::FROM, self::TO, $cloudTalk));
    }

    public function test_unavailable_remote_recording_falls_back_to_the_previous_one_and_is_cached(): void
    {
        $this->interaction('100', ['started_at' => '2026-10-19 08:00:00']);
        $this->interaction('101', ['started_at' => '2026-10-20 08:00:00']);
        $cloudTalk = Mockery::mock(CloudTalk::class);
        $cloudTalk->shouldReceive('recordingMedia')->once()->with(Mockery::type(Line::class), '101')
            ->andThrow(new RuntimeException('Missing recording', 404));
        $cloudTalk->shouldReceive('recordingMedia')->twice()->with(Mockery::type(Line::class), '100')
            ->andReturn(['body' => 'working-audio', 'content_type' => 'audio/x-wav']);

        $this->assertSame('working-audio', $this->service()->media(1, self::FROM, self::TO, $cloudTalk)['body']);
        $this->assertArrayHasKey(1, $this->service()->urls([1], self::FROM, self::TO));
        $this->assertSame('working-audio', $this->service()->media(1, self::FROM, self::TO, $cloudTalk)['body']);
    }

    public function test_all_remote_recordings_unavailable_returns_null_and_hides_the_cached_url(): void
    {
        $this->interaction('100');
        $cloudTalk = Mockery::mock(CloudTalk::class);
        $cloudTalk->shouldReceive('recordingMedia')->once()->with(Mockery::type(Line::class), '100')
            ->andThrow(new RuntimeException('Expired recording', 410));

        $this->assertArrayHasKey(1, $this->service()->urls([1], self::FROM, self::TO));
        $this->assertNull($this->service()->media(1, self::FROM, self::TO, $cloudTalk));
        $this->assertSame([], $this->service()->urls([1], self::FROM, self::TO));
        $this->assertNull($this->service()->media(1, self::FROM, self::TO, $cloudTalk));
    }

    public function test_temporary_provider_failure_is_propagated_and_does_not_hide_the_recording(): void
    {
        $this->interaction('100');
        $cloudTalk = Mockery::mock(CloudTalk::class);
        $cloudTalk->shouldReceive('recordingMedia')->once()->with(Mockery::type(Line::class), '100')
            ->andThrow(new RuntimeException('Provider unavailable', 503));

        try {
            $this->service()->media(1, self::FROM, self::TO, $cloudTalk);
            $this->fail('A temporary provider failure must be propagated.');
        } catch (RuntimeException $error) {
            $this->assertSame(503, $error->getCode());
        }

        $this->assertArrayHasKey(1, $this->service()->urls([1], self::FROM, self::TO));
    }

    public function test_cloudtalk_json_error_inside_http_success_is_not_returned_as_audio(): void
    {
        $this->interaction('100');
        Http::fake([
            'https://my.cloudtalk.io/api/calls/recording/100.json' => Http::response([
                'responseData' => ['status' => 404, 'message' => 'Recording not found'],
            ], 200),
        ]);

        $this->assertNull($this->service()->media(1, self::FROM, self::TO, new CloudTalk()));
        $this->assertSame([], $this->service()->urls([1], self::FROM, self::TO));
        Http::assertSentCount(1);
    }

    /** @dataProvider invalidMediaBodies */
    public function test_non_audio_provider_response_is_rejected(string $contentType, string $body): void
    {
        $this->interaction('100');
        Http::fake([
            'https://my.cloudtalk.io/api/calls/recording/100.json' => Http::response($body, 200, [
                'Content-Type' => $contentType,
            ]),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(503);
        $this->service()->media(1, self::FROM, self::TO, new CloudTalk());
    }

    public static function invalidMediaBodies(): array
    {
        return [
            'json' => ['application/json', '{"responseData":{"status":200}}'],
            'text' => ['text/plain', 'No audio media'],
        ];
    }

    public function test_recording_endpoint_returns_no_content_when_no_recording_is_available(): void
    {
        $cloudTalk = Mockery::mock(CloudTalk::class);
        $cloudTalk->shouldNotReceive('recordingMedia');
        $this->app->instance(CloudTalk::class, $cloudTalk);

        $this->getJson($this->endpoint())->assertNoContent();
        Http::assertNothingSent();
    }

    public function test_recording_endpoint_returns_the_previous_working_audio_when_latest_is_missing(): void
    {
        $this->interaction('100', ['started_at' => '2026-10-19 08:00:00']);
        $this->interaction('101', ['started_at' => '2026-10-20 08:00:00']);
        $cloudTalk = Mockery::mock(CloudTalk::class);
        $cloudTalk->shouldReceive('recordingMedia')->once()->with(Mockery::type(Line::class), '101')
            ->andThrow(new RuntimeException('Missing recording', 404));
        $cloudTalk->shouldReceive('recordingMedia')->once()->with(Mockery::type(Line::class), '100')
            ->andReturn(['body' => 'working-audio', 'content_type' => 'audio/x-wav']);
        $this->app->instance(CloudTalk::class, $cloudTalk);

        $this->getJson($this->endpoint())->assertOk()
            ->assertHeader('Content-Type', 'audio/x-wav')->assertContent('working-audio');
    }

    public function test_recording_endpoint_rejects_invalid_or_reversed_date_ranges(): void
    {
        $this->getJson($this->endpoint(1, ['from' => 'invalid']))
            ->assertUnprocessable()->assertJsonValidationErrors('from');
        $this->getJson($this->endpoint(1, ['from' => '2026-10-31', 'to' => '2026-10-01']))
            ->assertUnprocessable()->assertJsonValidationErrors('to');
    }

    public function test_recording_endpoint_rejects_a_user_outside_the_project(): void
    {
        $cloudTalk = Mockery::mock(CloudTalk::class);
        $cloudTalk->shouldNotReceive('recordingMedia');
        $this->app->instance(CloudTalk::class, $cloudTalk);

        $this->getJson($this->endpoint(3))->assertNotFound();
        Http::assertNothingSent();
    }

    protected function endpoint(int $userId = 1, array $dates = []): string
    {
        return '/api/project/recordings-test/stat-dashboard/recording/' . $userId . '?' . http_build_query(array_merge([
            'from' => '2026-10-01', 'to' => '2026-10-31',
        ], $dates));
    }

    protected function service(): DashboardRecordings
    {
        return new DashboardRecordings(Project::findOrFail(1));
    }

    protected function line(int $projectId, int $userId): void
    {
        DB::table('lines')->insert([
            'project_id' => $projectId, 'user_id' => $userId, 'operator' => 'cloudtalk',
            'config' => json_encode([
                'api_key_id' => 'fixture-key', 'api_key_secret' => 'fixture-secret', 'agent_id' => '42',
            ]),
        ]);
    }

    protected function interaction(string $callId, array $overrides = [], ?array $data = null): int
    {
        return DB::table('interactions')->insertGetId(array_merge([
            'prospect_id' => 1,
            'creator_id' => 1,
            'source' => 'cloudtalk',
            'status' => 'answered',
            'path' => null,
            'started_at' => '2026-10-20 08:00:00',
            'ended_at' => '2026-10-20 08:00:43',
            'created_at' => '2026-10-20 08:00:45',
            'updated_at' => '2026-10-20 08:00:45',
            'data' => json_encode($data ?? ['call_id' => $callId, 'recorded' => true]),
        ], $overrides));
    }

    protected function mediaMock(string $callId, string $body): CloudTalk
    {
        $cloudTalk = Mockery::mock(CloudTalk::class);
        $cloudTalk->shouldReceive('recordingMedia')->once()
            ->with(Mockery::on(fn ($line) => $line instanceof Line && $line->project_id === 1 && $line->user_id === 1), $callId)
            ->andReturn(['body' => $body, 'content_type' => 'audio/x-wav']);

        return $cloudTalk;
    }
}
