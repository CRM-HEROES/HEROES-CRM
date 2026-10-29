<?php

namespace Tests\Feature;

use App\Http\Middleware\UserTrackActivity;
use App\Jobs\SMS\CloudTalk as SendCloudTalk;
use App\Models\Sms;
use App\Services\CloudTalk;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CloudTalkOutgoingSmsWebhookTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'cloudtalk_outgoing_sms_test',
            'database.connections.cloudtalk_outgoing_sms_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
        $this->withoutMiddleware(UserTrackActivity::class);
        Http::preventStrayRequests();
        Notification::fake();

        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->softDeletes();
        });
        $schema->create('prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('phone_number')->nullable();
            $table->string('mobile_phone_number')->nullable();
            $table->softDeletes();
        });
        $schema->create('sms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->text('message');
            $table->json('media')->nullable();
            $table->text('error')->nullable();
            $table->string('source');
            $table->boolean('from_user')->default(true);
            $table->boolean('is_incoming')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->string('cloudtalk_message_id', 191)->nullable()->index();
            $table->string('cloudtalk_event_id', 191)->nullable()->index();
            $table->string('cloudtalk_sender_number', 32)->nullable();
            $table->timestamp('cloudtalk_send_started_at')->nullable();
            $table->timestamps();
        });
        $schema->create('lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
            $table->string('operator');
            $table->json('config');
        });
        $schema->create('user_project', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
        });
        DB::table('projects')->insert([
            ['id' => 1, 'slug' => 'sms-project'], ['id' => 2, 'slug' => 'other-project'],
        ]);
        DB::table('prospects')->insert([
            ['id' => 1, 'project_id' => 2, 'mobile_phone_number' => '06 12 34 56 78'],
            ['id' => 2, 'project_id' => 1, 'mobile_phone_number' => '06 12 34 56 78'],
            ['id' => 3, 'project_id' => 1, 'mobile_phone_number' => '+33123456789'],
        ]);
        DB::table('user_project')->insert([
            ['project_id' => 1, 'user_id' => 7], ['project_id' => 2, 'user_id' => 9],
        ]);
        DB::table('lines')->insert([
            ['id' => 1, 'project_id' => 1, 'user_id' => 7, 'operator' => 'cloudtalk', 'config' => json_encode([
                'agent_id' => 'agent-7', 'numero' => '+33123456789',
            ])],
            ['id' => 2, 'project_id' => 2, 'user_id' => 9, 'operator' => 'cloudtalk', 'config' => json_encode([
                'agent_id' => 'agent-9', 'numero' => '+33123456789',
            ])],
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        DB::purge('cloudtalk_outgoing_sms_test');
        parent::tearDown();
    }

    public function test_sms_sent_outside_crm_is_saved_as_outgoing_for_the_recipient_without_resending(): void
    {
        $this->postJson($this->endpoint(), $this->payload())->assertCreated()->assertJson(['prospect_id' => 2]);

        $sms = Sms::query()->sole();
        $this->assertSame('Votre rendez-vous est confirmé.', $sms->message);
        $this->assertSame(2, $sms->prospect_id);
        $this->assertSame(7, $sms->creator_id);
        $this->assertSame('cloudtalk', $sms->source);
        $this->assertTrue($sms->from_user);
        $this->assertFalse($sms->is_incoming);
        $this->assertSame('message-123', $sms->cloudtalk_message_id);
        $this->assertSame('event-123', $sms->cloudtalk_event_id);
        $this->assertSame('33123456789', $sms->cloudtalk_sender_number);
        $this->assertSame('2026-10-29 08:18:38', $sms->sent_at->format('Y-m-d H:i:s'));
        Notification::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_message_id_prevents_repeated_callbacks_even_with_a_different_event_id(): void
    {
        $first = $this->postJson($this->endpoint(), $this->payload())->assertCreated();
        $payload = $this->payload();
        $this->postJson($this->endpoint(), $payload)->assertOk()->assertJsonPath('sms_id', $first->json('sms_id'));
        $payload['event_id'] = 'different-retry-id';
        $payload['data']['created_at'] = '2026-10-29T08:19:38Z';
        $this->postJson($this->endpoint(), $payload)->assertOk()->assertJsonPath('sms_id', $first->json('sms_id'));

        $this->assertDatabaseCount('sms', 1);
        $this->assertSame('2026-10-29 08:18:38', Sms::query()->sole()->sent_at->format('Y-m-d H:i:s'));
        Notification::assertNothingSent();
    }

    public function test_event_id_alone_still_prevents_duplicate_notifications(): void
    {
        $payload = $this->payload();
        unset($payload['data']['id']);
        $first = $this->postJson($this->endpoint(), $payload)->assertCreated();
        $this->postJson($this->endpoint(), $payload)->assertOk()->assertJsonPath('sms_id', $first->json('sms_id'));
        $this->assertDatabaseCount('sms', 1);
    }

    public function test_outgoing_callback_without_a_stable_provider_reference_is_not_imported(): void
    {
        $payload = $this->payload();
        unset($payload['event_id'], $payload['data']['id']);
        $this->postJson($this->endpoint(), $payload)->assertOk()
            ->assertJson(['message' => 'ignored', 'reason' => 'missing_message_reference']);
        $this->assertDatabaseCount('sms', 0);
    }

    public function test_identical_text_with_different_provider_message_ids_remains_two_real_messages(): void
    {
        $this->postJson($this->endpoint(), $this->payload())->assertCreated();
        $payload = $this->payload();
        $payload['data']['id'] = 'message-124';
        $payload['event_id'] = 'event-124';
        $this->postJson($this->endpoint(), $payload)->assertCreated();
        $this->assertDatabaseCount('sms', 2);
    }

    public function test_already_sent_crm_sms_is_enriched_instead_of_duplicated(): void
    {
        $id = $this->crmSms(['sent_at' => '2026-10-29 08:18:37']);
        $this->postJson($this->endpoint(), $this->payload())->assertOk()->assertJsonPath('sms_id', $id);

        $this->assertDatabaseCount('sms', 1);
        $sms = Sms::query()->findOrFail($id);
        $this->assertSame('message-123', $sms->cloudtalk_message_id);
        $this->assertSame(7, $sms->creator_id);
        Notification::assertNothingSent();
    }

    public function test_legacy_sent_crm_sms_can_match_the_configured_creator_sender_without_a_stored_sender(): void
    {
        $id = $this->crmSms(['sent_at' => '2026-10-29 08:18:37', 'cloudtalk_sender_number' => null]);
        $this->postJson($this->endpoint(), $this->payload())->assertOk()->assertJsonPath('sms_id', $id);
        $this->assertDatabaseCount('sms', 1);
        $this->assertSame('33123456789', Sms::query()->sole()->cloudtalk_sender_number);
    }

    public function test_callback_before_crm_job_response_matches_in_flight_sms_and_preserves_original_media(): void
    {
        $media = [['path' => 'private/notes.png', 'file_name' => 'notes.png', 'mime_type' => 'image/png']];
        $id = $this->crmSms([
            'cloudtalk_send_started_at' => '2026-10-29 08:18:37', 'sent_at' => null,
            'media' => json_encode($media),
        ]);
        $payload = $this->payload();
        $payload['data']['media'] = [['url' => 'https://media.example.test/notes.png', 'file_name' => 'notes.png']];
        $this->postJson($this->endpoint(), $payload)->assertOk()->assertJsonPath('sms_id', $id);

        $this->assertDatabaseCount('sms', 1);
        $sms = Sms::query()->findOrFail($id);
        $this->assertSame($media, $sms->media);
        $this->assertNotNull($sms->sent_at);
        $this->assertSame('message-123', $sms->cloudtalk_message_id);
        Notification::assertNothingSent();
    }

    public function test_real_send_job_and_early_webhook_without_api_response_id_keep_one_crm_record(): void
    {
        Carbon::setTestNow('2026-10-29 08:18:37');
        $id = $this->crmSms();
        $service = \Mockery::mock(CloudTalk::class)->makePartial();
        $service->shouldReceive('sendSms')->once()->andReturnUsing(function () use ($id) {
            $this->assertNotNull(Sms::query()->findOrFail($id)->cloudtalk_send_started_at);
            $this->postJson($this->endpoint(), $this->payload())->assertOk()->assertJsonPath('sms_id', $id);

            return ['responseData' => ['success' => true]];
        });

        $job = new SendCloudTalk(Sms::query()->findOrFail($id));
        $job->handle($service);
        $job->handle($service);
        $this->postJson($this->endpoint(), $this->payload())->assertOk()->assertJsonPath('sms_id', $id);

        $this->assertDatabaseCount('sms', 1);
        $sms = Sms::query()->sole();
        $this->assertSame($id, $sms->id);
        $this->assertSame(7, $sms->creator_id);
        $this->assertSame('message-123', $sms->cloudtalk_message_id);
        $this->assertSame('event-123', $sms->cloudtalk_event_id);
        $this->assertNotNull($sms->sent_at);
        $this->assertNull($sms->error);
        Notification::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_webhook_after_successful_send_without_api_response_id_enriches_original_crm_sms(): void
    {
        Carbon::setTestNow('2026-10-29 08:18:37');
        $id = $this->crmSms();
        $service = \Mockery::mock(CloudTalk::class)->makePartial();
        $service->shouldReceive('sendSms')->once()->andReturn(['responseData' => ['success' => true]]);
        $job = new SendCloudTalk(Sms::query()->findOrFail($id));
        $job->handle($service);
        $sms = Sms::query()->findOrFail($id);
        $this->assertNotNull($sms->sent_at);
        $this->assertNull($sms->cloudtalk_message_id);

        $this->postJson($this->endpoint(), $this->payload())->assertOk()->assertJsonPath('sms_id', $id);
        $job->handle($service);
        $this->assertDatabaseCount('sms', 1);
        $this->assertSame('message-123', $sms->fresh()->cloudtalk_message_id);
        Notification::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_provider_id_matches_existing_crm_sms_even_if_text_or_time_has_changed(): void
    {
        $id = $this->crmSms([
            'cloudtalk_message_id' => 'message-123', 'message' => 'Texte original à conserver',
            'sent_at' => '2026-10-28 08:18:38',
        ]);
        $this->postJson($this->endpoint(), $this->payload())->assertOk()->assertJsonPath('sms_id', $id);
        $this->assertDatabaseCount('sms', 1);
        $this->assertSame('Texte original à conserver', Sms::query()->sole()->message);
    }

    public function test_content_matching_never_uses_another_sender_or_a_distant_send_time(): void
    {
        $this->crmSms(['cloudtalk_sender_number' => '33999999999', 'sent_at' => '2026-10-29 08:18:37']);
        $this->crmSms(['sent_at' => '2026-10-29 07:18:37']);
        $this->postJson($this->endpoint(), $this->payload())->assertCreated();
        $this->assertDatabaseCount('sms', 3);
    }

    public function test_sender_numbers_from_different_countries_are_not_merged_by_their_shared_local_suffix(): void
    {
        $this->crmSms(['sent_at' => '2026-10-29 08:18:37']);
        $payload = $this->payload();
        $payload['data']['internal_number']['number_e164'] = '+44123456789';
        $this->postJson($this->endpoint(), $payload)->assertCreated();
        $this->assertDatabaseCount('sms', 2);
    }

    public function test_failed_crm_sms_does_not_steal_a_successful_external_message(): void
    {
        $failedId = $this->crmSms([
            'cloudtalk_send_started_at' => '2026-10-29 08:18:37',
            'error' => 'CloudTalk: identifiants refusés.',
        ]);
        $this->postJson($this->endpoint(), $this->payload())->assertCreated();
        $this->assertDatabaseCount('sms', 2);
        $failed = Sms::query()->findOrFail($failedId);
        $this->assertNull($failed->cloudtalk_message_id);
        $this->assertSame('CloudTalk: identifiants refusés.', $failed->error);
    }

    public function test_missing_provider_timestamp_does_not_match_content_but_retries_still_use_message_id(): void
    {
        $this->crmSms(['sent_at' => '2026-10-29 08:18:37']);
        $payload = $this->payload();
        unset($payload['data']['created_at'], $payload['occurred_at']);
        $first = $this->postJson($this->endpoint(), $payload)->assertCreated();
        $this->postJson($this->endpoint(), $payload)->assertOk()->assertJsonPath('sms_id', $first->json('sms_id'));
        $this->assertDatabaseCount('sms', 2);
    }

    public function test_message_reference_and_phone_matching_stay_scoped_to_the_current_project(): void
    {
        $this->crmSms(['prospect_id' => 1, 'cloudtalk_message_id' => 'message-123', 'sent_at' => '2026-10-29 08:18:37']);
        $this->postJson($this->endpoint(), $this->payload())->assertCreated()->assertJson(['prospect_id' => 2]);
        $this->assertDatabaseCount('sms', 2);
    }

    public function test_unmatched_destination_does_not_record_a_message_against_the_sender(): void
    {
        $payload = $this->payload();
        $payload['data']['external_number'] = '+33999888777';
        $this->postJson($this->endpoint(), $payload)->assertOk()->assertJson(['reason' => 'prospect_not_found']);
        $this->assertDatabaseCount('sms', 0);
        Notification::assertNothingSent();
    }

    public function test_outgoing_to_number_fallback_uses_recipient_rather_than_sender(): void
    {
        $payload = $this->payload();
        unset($payload['data']['external_number']);
        $payload['data']['to_number'] = '+33612345678';
        $payload['data']['from_number'] = '+33123456789';
        $this->postJson($this->endpoint(), $payload)->assertCreated()->assertJson(['prospect_id' => 2]);
        $this->assertSame(2, Sms::query()->sole()->prospect_id);
    }

    public function test_sent_event_without_explicit_direction_also_works_through_legacy_sms_endpoint(): void
    {
        $payload = $this->payload();
        unset($payload['data']['direction']);
        $this->postJson($this->endpoint().'/sms', $payload)->assertCreated();
        $this->assertFalse(Sms::query()->sole()->is_incoming);
        $this->assertTrue(Sms::query()->sole()->from_user);
        Notification::assertNothingSent();
    }

    public function test_unsupported_message_status_events_are_not_imported_as_new_sent_messages(): void
    {
        $payload = $this->payload();
        $payload['type'] = 'message.failed';
        $this->postJson($this->endpoint(), $payload)->assertOk()->assertJson(['message' => 'ignored']);
        $this->assertDatabaseCount('sms', 0);
    }

    public function test_outgoing_whatsapp_messages_are_not_imported_as_sms(): void
    {
        $payload = $this->payload();
        $payload['data']['channel'] = 'whatsapp';
        $this->postJson($this->endpoint(), $payload)->assertOk()->assertJson(['message' => 'ignored']);
        $this->assertDatabaseCount('sms', 0);
        Notification::assertNothingSent();
    }

    public function test_media_only_message_does_not_collapse_a_different_crm_attachment(): void
    {
        $this->crmSms([
            'message' => '', 'sent_at' => '2026-10-29 08:18:37',
            'media' => json_encode([['path' => 'private/other.png', 'file_name' => 'other.png']]),
        ]);
        $payload = $this->payload();
        $payload['data']['body'] = '';
        $payload['data']['channel'] = 'mms';
        $payload['data']['media'] = [['url' => 'https://media.example.test/new.png', 'file_name' => 'new.png', 'extension' => 'png']];
        $this->postJson($this->endpoint(), $payload)->assertCreated();
        $this->assertDatabaseCount('sms', 2);
        $this->assertSame('new.png', Sms::query()->latest('id')->first()->attachments[0]['file_name']);
        Notification::assertNothingSent();
    }

    public function test_unknown_sender_agent_is_not_attributed_to_an_arbitrary_project_user(): void
    {
        $payload = $this->payload();
        $payload['data']['user']['id'] = 'unconfigured-agent';
        $this->postJson($this->endpoint(), $payload)->assertCreated();
        $this->assertNull(Sms::query()->sole()->creator_id);
    }

    public function test_received_messages_still_keep_incoming_direction_and_are_idempotent(): void
    {
        $payload = $this->payload();
        $payload['type'] = 'message.received';
        $payload['data']['direction'] = 'inbound';
        $first = $this->postJson($this->endpoint(), $payload)->assertCreated();
        $this->postJson($this->endpoint(), $payload)->assertOk()->assertJsonPath('sms_id', $first->json('sms_id'));
        $sms = Sms::query()->sole();
        $this->assertTrue($sms->is_incoming);
        $this->assertFalse($sms->from_user);
        $this->assertNull($sms->creator_id);
        Notification::assertNothingSent();
    }

    protected function endpoint(): string
    {
        return '/webhook/project/sms-project/cloudtalk';
    }

    protected function payload(): array
    {
        return [
            'type' => 'message.sent', 'event_id' => 'event-123', 'version' => 'v1',
            'occurred_at' => '2026-10-29T08:18:39Z',
            'data' => [
                'id' => 'message-123', 'direction' => 'outbound', 'channel' => 'sms',
                'body' => 'Votre rendez-vous est confirmé.',
                'external_number' => '+33612345678',
                'internal_number' => ['number_e164' => '+33123456789'],
                'created_at' => '2026-10-29T08:18:38Z',
                'user' => ['id' => 'agent-7'],
            ],
        ];
    }

    protected function crmSms(array $attributes = []): int
    {
        return DB::table('sms')->insertGetId(array_merge([
            'prospect_id' => 2, 'creator_id' => 7, 'source' => 'cloudtalk',
            'from_user' => true, 'is_incoming' => false,
            'message' => 'Votre rendez-vous est confirmé.',
            'cloudtalk_sender_number' => '33123456789',
            'created_at' => '2026-10-29 08:18:36', 'updated_at' => '2026-10-29 08:18:36',
        ], $attributes));
    }
}
