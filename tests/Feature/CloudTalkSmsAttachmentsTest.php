<?php

namespace Tests\Feature;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\ProjectUserAccessCheck;
use App\Http\Middleware\UserLastProjectCheck;
use App\Http\Middleware\UserTrackActivity;
use App\Jobs\SMS\CloudTalk as SendCloudTalk;
use App\Models\Line;
use App\Models\Sms;
use App\Models\User;
use App\Services\CloudTalk;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Contracts\Queue\Job as QueueJob;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CloudTalkSmsAttachmentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'cloudtalk_attachments_test',
            'database.connections.cloudtalk_attachments_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
        Event::fake([Authenticated::class]);
        Notification::fake();
        Storage::fake('sms');
        Http::preventStrayRequests();
        $this->withoutMiddleware([
            Authenticate::class, UserTrackActivity::class, ProjectUserAccessCheck::class, UserLastProjectCheck::class,
        ]);

        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Agent');
            $table->string('role')->default('super_admin');
        });
        $schema->create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
        });
        $schema->create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->softDeletes();
        });
        $schema->create('prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('mobile_phone_number');
            $table->softDeletes();
        });
        $schema->create('sms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->text('message');
            $table->text('error')->nullable();
            $table->string('source');
            $table->boolean('from_user')->default(true);
            $table->boolean('is_incoming')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_10_20_000001_add_media_to_sms_table.php'))->up();
        (require database_path('migrations/2026_10_29_000002_add_cloudtalk_references_to_sms_table.php'))->up();
        $schema->create('lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
            $table->string('operator');
            $table->json('config');
        });
        DB::table('users')->insert(['id' => 1]);
        DB::table('projects')->insert([['id' => 1, 'slug' => 'test'], ['id' => 2, 'slug' => 'other']]);
        DB::table('prospects')->insert([
            ['id' => 1, 'project_id' => 1, 'mobile_phone_number' => '+12025550143'],
            ['id' => 2, 'project_id' => 1, 'mobile_phone_number' => '+12025550144'],
            ['id' => 3, 'project_id' => 2, 'mobile_phone_number' => '+12025550145'],
        ]);
        DB::table('lines')->insert([
            'id' => 1, 'project_id' => 1, 'user_id' => 1, 'operator' => 'cloudtalk',
            'config' => json_encode(['numero' => '+12025550178', 'api_key_id' => 'test', 'api_key_secret' => 'secret']),
        ]);
        $this->actingAs(User::query()->findOrFail(1));
    }

    protected function tearDown(): void
    {
        DB::purge('cloudtalk_attachments_test');
        parent::tearDown();
    }

    public function test_image_only_message_is_stored_privately_and_the_job_uploads_its_binary_to_cloudtalk(): void
    {
        $response = $this->post($this->endpoint(), [
            'source' => 'cloudtalk', 'message' => '', 'attachments' => [$this->image()],
        ], ['Accept' => 'application/json'])->assertCreated()
            ->assertJsonMissingPath('media')->assertJsonMissingPath('prospect');
        $sms = Sms::query()->sole();
        $this->assertSame('', $sms->message);
        Storage::disk('sms')->assertExists($sms->media[0]['path']);
        $this->assertSame('photo.png', $response->json('attachments.0.file_name'));
        $this->get($response->json('attachments.0.url'))->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->getJson($this->endpoint())->assertOk()
            ->assertJsonPath('0.attachments.0.url', $response->json('attachments.0.url'))
            ->assertJsonMissingPath('0.media')->assertJsonMissingPath('0.prospect');

        Http::fake(function (Request $request) {
            // Inspect the body while the upload streams are open, as the HTTP transport would.
            $this->assertStringContainsString($this->imageBytes(), $request->body());
            $this->assertStringContainsString('+12025550143', $request->body());
            return Http::response(['responseData' => ['success' => true]]);
        });
        (new SendCloudTalk($sms))->handle(new CloudTalk());
        Http::assertSent(fn (Request $request) => $request->isMultipart()
            && $request->hasFile('images[0]', null, 'photo.png'));
        $this->assertNotNull($sms->fresh()->sent_at);
        $this->assertNull($sms->fresh()->error);
    }

    public function test_text_only_sms_keeps_the_original_json_payload(): void
    {
        Http::fake(['my.cloudtalk.io/api/sms/send.json' => Http::response(['responseData' => ['success' => true]])]);
        (new CloudTalk())->sendSms(Line::query()->findOrFail(1), '+12025550143', 'Bonjour');
        Http::assertSent(fn (Request $request) => $request->isJson() && $request->data() === [
            'recipient' => '+12025550143', 'sender' => '+12025550178', 'message' => 'Bonjour',
        ]);
    }

    public function test_job_records_provider_identity_and_does_not_resend_a_completed_sms(): void
    {
        $this->post($this->endpoint(), ['source' => 'cloudtalk', 'message' => 'Bonjour'], ['Accept' => 'application/json'])
            ->assertCreated();
        $sms = Sms::query()->sole();
        Http::fake(['my.cloudtalk.io/api/sms/send.json' => Http::response([
            'responseData' => ['success' => true, 'data' => ['message_id' => 'provider-message-1']],
        ])]);

        (new SendCloudTalk($sms))->handle(new CloudTalk());
        $sent = $sms->fresh();
        $this->assertSame('provider-message-1', $sent->cloudtalk_message_id);
        $this->assertSame('12025550178', $sent->cloudtalk_sender_number);
        $this->assertNotNull($sent->cloudtalk_send_started_at);
        $this->assertNotNull($sent->sent_at);
        (new SendCloudTalk($sms))->handle(new CloudTalk());
        Http::assertSentCount(1);
        $this->getJson($this->endpoint())->assertOk()->assertJsonMissingPath('0.cloudtalk_message_id')
            ->assertJsonMissingPath('0.cloudtalk_sender_number');
    }

    public function test_duplicate_queue_delivery_is_discarded_while_a_send_owns_the_lock(): void
    {
        $this->post($this->endpoint(), ['source' => 'cloudtalk', 'message' => 'Bonjour'], ['Accept' => 'application/json'])
            ->assertCreated();
        $sms = Sms::query()->sole();
        $job = new SendCloudTalk($sms);
        $queueJob = \Mockery::mock(QueueJob::class);
        $queueJob->shouldNotReceive('release');
        $job->setJob($queueJob);
        $middleware = $job->middleware()[0];
        $lock = Cache::lock($middleware->getLockKey($job), 120);
        $this->assertTrue($lock->get());
        $deliveries = 0;
        $handler = function () use (&$deliveries) { $deliveries++; };
        try {
            $middleware->handle($job, $handler);
            $this->assertSame(0, $deliveries);
        } finally {
            $lock->release();
        }
        $middleware->handle($job, $handler);
        $this->assertSame(1, $deliveries);
        Http::assertNothingSent();
    }

    public function test_early_callback_identity_survives_a_send_response_without_an_id(): void
    {
        $this->post($this->endpoint(), ['source' => 'cloudtalk', 'message' => 'Bonjour'], ['Accept' => 'application/json'])
            ->assertCreated();
        $sms = Sms::query()->sole();
        Http::fake(function () use ($sms) {
            $inFlight = $sms->fresh();
            $this->assertSame('12025550178', $inFlight->cloudtalk_sender_number);
            $this->assertNotNull($inFlight->cloudtalk_send_started_at);
            $this->assertNull($inFlight->sent_at);
            DB::table('sms')->where('id', $sms->id)->update([
                'cloudtalk_message_id' => 'early-provider-id', 'cloudtalk_event_id' => 'early-event-id',
                'sent_at' => '2026-10-29 10:00:00',
            ]);

            return Http::response(['responseData' => ['success' => true]]);
        });

        (new SendCloudTalk($sms))->handle(new CloudTalk());
        $sent = $sms->fresh();
        $this->assertSame('early-provider-id', $sent->cloudtalk_message_id);
        $this->assertSame('early-event-id', $sent->cloudtalk_event_id);
        $this->assertSame('2026-10-29 10:00:00', $sent->sent_at->format('Y-m-d H:i:s'));
        $this->assertNull($sent->error);
        $this->assertDatabaseCount('sms', 1);
    }

    public function test_provider_response_merges_an_early_import_without_replacing_crm_id_or_uploaded_media(): void
    {
        $this->post($this->endpoint(), [
            'source' => 'cloudtalk', 'message' => 'Photo', 'attachments' => [$this->image()],
        ], ['Accept' => 'application/json'])->assertCreated();
        $sms = Sms::query()->sole();
        $path = $sms->media[0]['path'];
        Http::fake(function () use ($sms) {
            DB::table('sms')->insert([
                'prospect_id' => $sms->prospect_id, 'source' => 'cloudtalk', 'message' => 'Photo',
                'from_user' => true, 'is_incoming' => false, 'cloudtalk_message_id' => 'same-provider-id',
                'cloudtalk_event_id' => 'same-event-id', 'sent_at' => now(),
                'created_at' => now(), 'updated_at' => now(),
            ]);

            return Http::response(['responseData' => ['success' => true, 'data' => ['message_id' => 'same-provider-id']]]);
        });

        (new SendCloudTalk($sms))->handle(new CloudTalk());
        $sent = Sms::query()->sole();
        $this->assertSame($sms->id, $sent->id);
        $this->assertSame(1, $sent->creator_id);
        $this->assertSame($path, $sent->media[0]['path']);
        $this->assertSame('same-provider-id', $sent->cloudtalk_message_id);
        $this->assertSame('same-event-id', $sent->cloudtalk_event_id);
        Storage::disk('sms')->assertExists($path);
    }

    public function test_message_reference_extractor_accepts_only_explicit_scalar_message_fields(): void
    {
        $service = new CloudTalk();
        $this->assertSame('123', $service->smsMessageId(['responseData' => ['data' => ['message_id' => 123]]]));
        $this->assertSame('message-uuid', $service->smsMessageId(['data' => ['id' => 'message-uuid']]));
        foreach ([
            ['id' => 'event-id', 'event_id' => 'event-id'],
            ['responseData' => ['success' => true, 'data' => ['recipient' => '123', 'message' => 'text']]],
            ['responseData' => ['data' => ['id' => ['nested' => 'not-an-id']]]],
            ['message_id' => true], ['sms_id' => str_repeat('x', 192)],
        ] as $response) {
            $this->assertNull($service->smsMessageId($response));
        }
    }

    public function test_cloudtalk_provider_rejection_is_not_reported_as_success(): void
    {
        Storage::disk('sms')->put('photo.png', $this->imageBytes());
        Http::fake(['my.cloudtalk.io/api/sms/send.json' => Http::response([
            'responseData' => ['success' => 'false', 'data' => 'MMS disabled'],
        ])]);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('MMS disabled');
        (new CloudTalk())->sendSms(Line::query()->findOrFail(1), '+12025550143', '', [[
            'path' => 'photo.png', 'file_name' => 'photo.png', 'mime_type' => 'image/png',
        ]]);
    }

    public function test_bulk_messages_keep_independent_attachments_for_each_permitted_prospect(): void
    {
        $this->post('/api/project/test/sms/bulk', [
            'source' => 'cloudtalk', 'message' => 'Photo', 'prospects' => [1, 2, 3], 'attachments' => [$this->image()],
        ], ['Accept' => 'application/json'])->assertOk()->assertJson(['count' => 2]);
        $messages = Sms::query()->orderBy('id')->get();
        $this->assertCount(2, $messages);
        $this->assertNotSame($messages[0]->media[0]['path'], $messages[1]->media[0]['path']);
        $messages[0]->delete();
        Storage::disk('sms')->assertExists($messages[1]->media[0]['path']);
    }

    public function test_attachment_cannot_be_read_through_another_prospect_or_project(): void
    {
        $this->post($this->endpoint(), ['source' => 'cloudtalk', 'attachments' => [$this->image()]], ['Accept' => 'application/json'])
            ->assertCreated();
        $sms = Sms::query()->sole();
        $this->get('/api/project/test/prospect/2/sms/' . $sms->id . '/attachment/0')->assertNotFound();
        $this->get('/api/project/other/prospect/1/sms/' . $sms->id . '/attachment/0')->assertNotFound();
        $this->get($this->endpoint() . '/' . $sms->id . '/attachment/99')->assertNotFound();
    }

    /** @dataProvider invalidAttachments */
    public function test_invalid_attachments_are_rejected_before_storage(string $case): void
    {
        $data = ['source' => 'cloudtalk', 'message' => 'Bonjour', 'attachments' => [$this->image()]];
        if ($case === 'format') {
            $data['attachments'] = [UploadedFile::fake()->createWithContent('document.pdf', '%PDF-1.4 example')];
        } elseif ($case === 'count') {
            $data['attachments'] = array_map(fn () => $this->image(), range(1, 6));
        } elseif ($case === 'total') {
            $data['attachments'] = [$this->image()->size(3000), $this->image()->size(3000)];
        } else {
            $data['source'] = 'smsbox';
        }
        $this->post($this->endpoint(), $data, ['Accept' => 'application/json'])->assertUnprocessable();
        $this->assertSame(0, Sms::query()->count());
        $this->assertSame([], Storage::disk('sms')->allFiles());
        Notification::assertNothingSent();
    }

    public static function invalidAttachments(): array
    {
        return [['format'], ['count'], ['total'], ['operator']];
    }

    public function test_deleting_sms_removes_its_uploaded_images(): void
    {
        $this->post($this->endpoint(), ['source' => 'cloudtalk', 'attachments' => [$this->image()]], ['Accept' => 'application/json'])
            ->assertCreated();
        $sms = Sms::query()->sole();
        $path = $sms->media[0]['path'];
        $this->deleteJson($this->endpoint() . '/' . $sms->id)->assertOk();
        Storage::disk('sms')->assertMissing($path);
    }

    protected function endpoint(): string
    {
        return '/api/project/test/prospect/1/sms';
    }

    protected function image(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('photo.png', $this->imageBytes());
    }

    protected function imageBytes(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j4p8AAAAASUVORK5CYII=');
    }
}
