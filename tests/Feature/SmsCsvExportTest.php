<?php

namespace Tests\Feature;

use App\Exports\SmsCsvExport;
use App\Exports\SmsDownload;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\ProjectUserAccessCheck;
use App\Http\Middleware\UserLastProjectCheck;
use App\Http\Middleware\UserTrackActivity;
use App\Jobs\DeleteSmsDownload;
use App\Jobs\PrepareSmsDownload;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class SmsCsvExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sms_export_test',
            'database.connections.sms_export_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
        Event::fake([Authenticated::class]);
        Http::preventStrayRequests();
        Queue::fake();
        Storage::fake('exports');
        $this->withoutMiddleware([
            Authenticate::class, ProjectUserAccessCheck::class, UserLastProjectCheck::class, UserTrackActivity::class,
        ]);
        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('role')->nullable();
            $table->string('name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
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
        $schema->create('prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('mobile_phone_number')->nullable();
            $table->softDeletes();
        });
        // Match the actual SMS schema: project ownership comes from its prospect.
        $schema->create('sms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospect_id')->nullable();
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->text('message');
            $table->text('error')->nullable();
            $table->string('source')->nullable();
            $table->boolean('from_user')->default(true);
            $table->boolean('is_incoming')->default(false);
            $table->json('media')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
        DB::table('users')->insert([
            ['id' => 1, 'role' => 'super_admin', 'name' => 'Super', 'email' => 'super@example.test'],
            ['id' => 2, 'role' => null, 'name' => 'Member', 'email' => 'member@example.test'],
            ['id' => 3, 'role' => null, 'name' => 'Admin', 'email' => 'admin@example.test'],
        ]);
        DB::table('projects')->insert([
            ['id' => 1, 'slug' => 'test', 'creator_id' => 3],
            ['id' => 2, 'slug' => 'other', 'creator_id' => 1],
        ]);
        DB::table('user_project')->insert([
            ['user_id' => 2, 'project_id' => 1], ['user_id' => 3, 'project_id' => 1],
        ]);
        DB::table('prospects')->insert([
            ['id' => 1, 'project_id' => 1, 'first_name' => 'Élodie', 'last_name' => 'Durand', 'phone_number' => '+33123456789', 'deleted_at' => null],
            ['id' => 2, 'project_id' => 2, 'first_name' => 'Other', 'last_name' => 'Project', 'phone_number' => null, 'deleted_at' => null],
            ['id' => 3, 'project_id' => 1, 'first_name' => 'Deleted', 'last_name' => 'Prospect', 'phone_number' => null, 'deleted_at' => '2026-10-23 12:00:00'],
        ]);
        $this->actingAs(User::findOrFail(1));
    }

    protected function tearDown(): void
    {
        DB::purge('sms_export_test');
        parent::tearDown();
    }

    public function test_project_admin_and_super_admin_can_prepare_and_download_a_private_csv(): void
    {
        $this->insertSms();
        foreach ([1, 3] as $userId) {
            $this->actingAs(User::findOrFail($userId));
            $response = $this->prepareCsvExport();
            $this->assertInstanceOf(\Symfony\Component\HttpFoundation\BinaryFileResponse::class, $response->baseResponse);
            $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
            $this->assertStringContainsString('sms-2026-10-23-2026-10-23.csv', $response->headers->get('Content-Disposition'));
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
            $this->assertCount(1, $this->csvRows($response));
        }
        Http::assertNothingSent();
    }

    public function test_export_is_queued_and_only_downloadable_after_preparation(): void
    {
        config(['queue.default' => 'redis']);
        $this->insertSms();
        $response = $this->postJson($this->endpoint(), $this->payload())->assertAccepted();
        $id = $this->exportId($response);
        $this->assertTrue(Str::isUuid($id));
        Queue::assertPushedOn('imports', PrepareSmsDownload::class);
        $expiresAt = CarbonImmutable::parse(app(SmsDownload::class)->status($id)['expires_at']);
        Queue::assertPushedOn('imports', DeleteSmsDownload::class, fn ($job) =>
            $job->exportId === $id && $job->delay instanceof \DateTimeInterface
            && $job->delay->getTimestamp() === $expiresAt->getTimestamp());
        $this->assertSame([], $this->csvFiles());
        $this->getJson($response->json('status_url'))->assertOk()->assertJsonPath('status', 'queued');
        $this->getJson($response->json('download_url'))->assertStatus(409);

        app(SmsDownload::class)->prepare($id);

        $this->getJson($response->json('status_url'))->assertOk()
            ->assertJsonPath('status', 'ready')->assertJsonPath('rows', 1);
        $this->assertCount(1, $this->csvFiles());
        $this->assertCount(1, $this->csvRows($this->get($response->json('download_url'))->assertOk()));
        Http::assertNothingSent();
    }

    public function test_status_and_download_require_the_owner_and_current_project_administration(): void
    {
        $response = $this->postJson($this->endpoint(), $this->payload())->assertAccepted();
        app(SmsDownload::class)->prepare($this->exportId($response));
        $this->actingAs(User::findOrFail(3));
        $this->getJson($response->json('status_url'))->assertForbidden();
        $this->getJson($response->json('download_url'))->assertForbidden();
        $this->actingAs(User::findOrFail(2));
        $this->getJson($response->json('status_url'))->assertForbidden();
        $this->getJson($response->json('download_url'))->assertForbidden();

        $this->actingAs(User::findOrFail(1));
        $this->getJson(str_replace('/test/', '/other/', $response->json('status_url')))->assertNotFound();
        $this->getJson(str_replace('/test/', '/other/', $response->json('download_url')))->assertNotFound();

        $this->actingAs(User::findOrFail(3));
        $owned = $this->postJson($this->endpoint(), $this->payload())->assertAccepted();
        app(SmsDownload::class)->prepare($this->exportId($owned));
        DB::table('projects')->where('id', 1)->update(['creator_id' => 1]);
        $this->getJson($owned->json('status_url'))->assertForbidden();
        $this->getJson($owned->json('download_url'))->assertForbidden();
    }

    public function test_unknown_and_invalid_export_ids_cannot_be_used(): void
    {
        foreach ([Str::uuid()->toString(), 'invalid-id'] as $id) {
            $this->getJson($this->endpoint() . '/' . $id . '/status')->assertNotFound();
            $this->getJson($this->endpoint() . '/' . $id . '/download')->assertNotFound();
        }
    }

    public function test_ordinary_member_is_forbidden_and_cannot_access_another_project(): void
    {
        $this->actingAs(User::findOrFail(2));
        $this->postJson($this->endpoint(), $this->payload())->assertForbidden();
        $this->postJson('/api/project/other/sms/export', $this->payload())->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_guest_cannot_export_sms(): void
    {
        $this->withMiddleware(Authenticate::class);
        $this->app['auth']->forgetGuards();
        $this->postJson($this->endpoint(), $this->payload())->assertUnauthorized();
        Http::assertNothingSent();
    }

    public function test_export_includes_all_sources_and_deleted_prospects_but_no_other_project_or_orphans(): void
    {
        $this->insertSms(['id' => 1, 'source' => 'cloudtalk', 'is_incoming' => true, 'from_user' => false]);
        $this->insertSms(['id' => 2, 'source' => 'twilio', 'sent_at' => '2026-10-24 00:00:00']);
        $this->insertSms(['id' => 3, 'source' => null, 'prospect_id' => 3, 'from_user' => false]);
        $this->insertSms(['id' => 4, 'prospect_id' => 2]);
        $this->insertSms(['id' => 5, 'prospect_id' => null]);
        $this->insertSms(['id' => 6, 'prospect_id' => 999]);
        $rows = $this->csvRows($this->prepareCsvExport());
        $this->assertSame(['1', '2', '3'], array_column($rows, 'sms_id'));
        $this->assertSame(['cloudtalk', 'twilio', ''], array_column($rows, 'source'));
        $this->assertSame(['incoming', 'outgoing', 'incoming'], array_column($rows, 'direction'));
        $this->assertSame('2026-10-23 12:00:00', $rows[2]['prospect_deleted_at_utc']);
        Http::assertNothingSent();
    }

    public function test_madagascar_date_bounds_filter_creation_time_not_sent_time(): void
    {
        foreach ([
            ['id' => 1, 'created_at' => '2026-10-22 20:59:59', 'sent_at' => '2026-10-23 10:00:00'],
            ['id' => 2, 'created_at' => '2026-10-22 21:00:00', 'sent_at' => null],
            ['id' => 3, 'created_at' => '2026-10-23 20:59:59', 'sent_at' => '2026-10-24 10:00:00'],
            ['id' => 4, 'created_at' => '2026-10-23 21:00:00', 'sent_at' => '2026-10-23 10:00:00'],
        ] as $sms) {
            $this->insertSms($sms);
        }
        $rows = $this->csvRows($this->prepareCsvExport(['timezone' => 'Indian/Antananarivo']));
        $this->assertSame(['2', '3'], array_column($rows, 'sms_id'));
        $this->assertSame(['2026-10-22 21:00:00', '2026-10-23 20:59:59'], array_column($rows, 'created_at_utc'));
    }

    public function test_daylight_saving_day_has_the_correct_local_date_bounds(): void
    {
        foreach ([
            ['id' => 1, 'created_at' => '2026-03-28 22:59:59'],
            ['id' => 2, 'created_at' => '2026-03-28 23:00:00'],
            ['id' => 3, 'created_at' => '2026-03-29 21:59:59'],
            ['id' => 4, 'created_at' => '2026-03-29 22:00:00'],
        ] as $sms) {
            $this->insertSms($sms);
        }
        $rows = $this->csvRows($this->prepareCsvExport([
            'date_from' => '2026-03-29', 'date_to' => '2026-03-29', 'timezone' => 'Europe/Paris',
        ]));
        $this->assertSame(['2', '3'], array_column($rows, 'sms_id'));
    }

    public function test_csv_preserves_unicode_quotes_and_newlines_and_keeps_original_formula_values_in_raw_json(): void
    {
        $message = "=Évaluer(\"été\");\nDeuxième ligne";
        $this->insertSms([
            'message' => $message, 'creator_id' => 3,
            'media' => json_encode([['url' => 'https://example.test/image.png', 'file_name' => 'été.png']], JSON_UNESCAPED_UNICODE),
        ]);
        $response = $this->prepareCsvExport();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $this->csvContent($response));
        $row = $this->csvRows($response)[0];
        $this->assertSame("'" . $message, $row['message']);
        $this->assertSame("'+33123456789", $row['prospect_phone_number']);
        $this->assertStringContainsString('Élodie', $row['prospect_name']);
        $this->assertSame('admin@example.test', $row['creator_email']);
        $this->assertSame($message, json_decode($row['raw_json'], true)['message']);
        $this->assertSame('été.png', json_decode($row['media_json'], true)[0]['file_name']);
        Http::assertNothingSent();
    }

    public function test_statuses_distinguish_received_sent_failed_and_pending_messages(): void
    {
        $this->insertSms(['id' => 1, 'is_incoming' => true]);
        $this->insertSms(['id' => 2, 'from_user' => false]);
        $this->insertSms(['id' => 3, 'sent_at' => '2026-10-23 11:00:00']);
        $this->insertSms(['id' => 4, 'error' => 'Provider rejected']);
        $this->insertSms(['id' => 5]);
        $this->insertSms(['id' => 6, 'creator_id' => null]);
        $rows = $this->csvRows($this->prepareCsvExport());
        $this->assertSame(['received', 'received', 'sent', 'failed', 'pending', 'pending'], array_column($rows, 'status'));
        $this->assertSame('', $rows[5]['creator_name']);
    }

    public function test_more_than_one_batch_exports_every_sms_once(): void
    {
        for ($id = 1; $id <= 1003; $id++) {
            $this->insertSms(['id' => $id, 'message' => 'Message ' . $id]);
        }
        $rows = $this->csvRows($this->prepareCsvExport());
        $this->assertCount(1003, $rows);
        $this->assertSame(array_map('strval', range(1, 1003)), array_column($rows, 'sms_id'));
        Http::assertNothingSent();
    }

    public function test_count_limits_the_first_matching_sms_and_zero_exports_all_rows(): void
    {
        for ($id = 1; $id <= 1003; $id++) {
            $this->insertSms(['id' => $id, 'message' => 'Message ' . $id]);
        }
        $limited = $this->csvRows($this->prepareCsvExport(['count' => 1000]));
        $this->assertCount(1000, $limited);
        $this->assertSame(array_map('strval', range(1, 1000)), array_column($limited, 'sms_id'));
        $unlimited = $this->csvRows($this->prepareCsvExport(['count' => 0]));
        $this->assertCount(1003, $unlimited);
        $this->assertSame(array_map('strval', range(1, 1003)), array_column($unlimited, 'sms_id'));
    }

    public function test_failed_writes_never_publish_a_partial_csv(): void
    {
        $this->insertSms();
        $response = $this->postJson($this->endpoint(), $this->payload())->assertAccepted();
        $id = $this->exportId($response);
        $writer = \Mockery::mock(SmsCsvExport::class);
        $writer->shouldReceive('write')->once()->andReturnUsing(function ($stream): int {
            fwrite($stream, 'partial export');
            throw new \RuntimeException('Simulated CSV write failure.');
        });
        $this->app->instance(SmsCsvExport::class, $writer);

        try {
            app(SmsDownload::class)->prepare($id);
            $this->fail('The CSV write failure should reach the queued job.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated CSV write failure.', $exception->getMessage());
        }

        $this->getJson($response->json('status_url'))->assertOk()->assertJsonPath('status', 'failed');
        $this->getJson($response->json('download_url'))->assertStatus(409);
        $this->assertSame([], $this->csvFiles());
        $this->assertSame([], array_values(array_filter(Storage::disk('exports')->allFiles(),
            fn ($path) => str_contains(basename($path), 'pending-'))));
    }

    public function test_queued_job_restores_the_previous_user_request_and_permission_team(): void
    {
        $this->insertSms();
        $response = $this->postJson($this->endpoint(), $this->payload())->assertAccepted();
        $id = $this->exportId($response);
        DB::table('users')->insert([
            'id' => 4, 'role' => null, 'name' => 'Unrelated worker user', 'email' => 'unrelated@example.test',
        ]);
        $this->actingAs(User::findOrFail(4));
        request()->replace(['sentinel' => 'preserved']);
        setPermissionsTeamId(987);

        (new PrepareSmsDownload($id))->handle(app(SmsDownload::class));

        $this->assertSame(4, auth()->id());
        $this->assertSame(['sentinel' => 'preserved'], request()->all());
        $this->assertSame(987, getPermissionsTeamId());
        $this->assertSame('ready', app(SmsDownload::class)->status($id)['status']);
        $this->assertSame(1, app(SmsDownload::class)->status($id)['rows']);
    }

    public function test_queued_job_rechecks_permission_and_restores_context_when_permission_was_revoked(): void
    {
        $this->actingAs(User::findOrFail(3));
        $response = $this->postJson($this->endpoint(), $this->payload())->assertAccepted();
        $id = $this->exportId($response);
        DB::table('projects')->where('id', 1)->update(['creator_id' => 1]);
        $this->actingAs(User::findOrFail(1));
        request()->replace(['sentinel' => 'preserved after failure']);
        setPermissionsTeamId(987);
        $job = new PrepareSmsDownload($id);

        try {
            $job->handle(app(SmsDownload::class));
            $this->fail('A revoked administrator must not prepare an export.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('ne peut plus exporter', $exception->getMessage());
            $job->failed($exception);
        }

        $this->assertSame(1, auth()->id());
        $this->assertSame(['sentinel' => 'preserved after failure'], request()->all());
        $this->assertSame(987, getPermissionsTeamId());
        $this->assertSame('failed', app(SmsDownload::class)->status($id)['status']);
        $this->assertSame([], $this->csvFiles());
    }

    public function test_expired_exports_are_removed_and_cannot_be_downloaded(): void
    {
        $this->insertSms();
        $response = $this->postJson($this->endpoint(), $this->payload())->assertAccepted();
        app(SmsDownload::class)->prepare($this->exportId($response));
        $this->assertCount(1, $this->csvFiles());

        $this->travel(25)->hours();

        $this->getJson($response->json('status_url'))->assertNotFound();
        $this->getJson($response->json('download_url'))->assertNotFound();
        $this->assertSame([], Storage::disk('exports')->allFiles());
        $this->travelBack();
    }

    public function test_empty_export_has_a_csv_header_and_no_data_rows(): void
    {
        $response = $this->prepareCsvExport();
        $this->assertSame([], $this->csvRows($response));
        $this->assertStringContainsString('sms_id', $this->csvContent($response));
        $this->assertStringContainsString('raw_json', $this->csvContent($response));
    }

    public function test_invalid_or_missing_dates_and_timezone_are_rejected(): void
    {
        foreach ([
            ['date_from' => null], ['date_to' => null], ['date_from' => '2026-02-30'],
            ['date_from' => '2026-10-24'], ['date_to' => '2026-10-22'],
            ['timezone' => null], ['timezone' => 'invalid'], ['date_from' => '23/10/2026'],
        ] as $invalid) {
            $this->postJson($this->endpoint(), $this->payload($invalid))->assertUnprocessable();
        }
        Http::assertNothingSent();
    }

    public function test_invalid_row_counts_are_rejected_before_dispatch(): void
    {
        foreach ([-1, 'many', 1.5, null] as $count) {
            $this->postJson($this->endpoint(), $this->payload(['count' => $count]))->assertUnprocessable();
        }
        Queue::assertNothingPushed();
    }

    protected function insertSms(array $overrides = []): void
    {
        DB::table('sms')->insert(array_merge([
            'prospect_id' => 1, 'creator_id' => 3, 'message' => 'Bonjour', 'source' => 'cloudtalk',
            'from_user' => true, 'is_incoming' => false, 'created_at' => '2026-10-23 10:00:00',
        ], $overrides));
    }

    protected function payload(array $overrides = []): array
    {
        return array_merge(['date_from' => '2026-10-23', 'date_to' => '2026-10-23', 'timezone' => 'UTC'], $overrides);
    }

    protected function endpoint(): string
    {
        return '/api/project/test/sms/export';
    }

    protected function prepareCsvExport(array $overrides = [])
    {
        $response = $this->postJson($this->endpoint(), $this->payload($overrides))->assertAccepted();
        app(SmsDownload::class)->prepare($this->exportId($response));
        return $this->get($response->json('download_url'))->assertOk();
    }

    protected function exportId($response): string
    {
        return basename(dirname(parse_url($response->json('download_url'), PHP_URL_PATH)));
    }

    protected function csvContent($response): string
    {
        return file_get_contents($response->baseResponse->getFile()->getPathname());
    }

    protected function csvFiles(): array
    {
        return array_values(array_filter(Storage::disk('exports')->allFiles(),
            fn ($path) => str_ends_with($path, '.csv')));
    }

    protected function csvRows($response): array
    {
        $csv = $this->csvContent($response);
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, substr($csv, 3));
        rewind($stream);
        $header = fgetcsv($stream, 0, ',', '"', '');
        $this->assertIsArray($header);
        $rows = [];
        while (($values = fgetcsv($stream, 0, ',', '"', '')) !== false) {
            $this->assertCount(count($header), $values);
            $rows[] = array_combine($header, $values);
        }
        fclose($stream);
        return $rows;
    }
}
