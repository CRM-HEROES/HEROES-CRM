<?php

namespace Tests\Feature;

use App\Http\Middleware\UserTrackActivity;
use App\Models\Interaction;
use App\Models\Sms;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CloudTalkWebhookTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Never run application migrations or touch the configured project database.
        config([
            'database.default' => 'cloudtalk_webhook_test',
            'database.connections.cloudtalk_webhook_test' => [
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
            $table->unsignedBigInteger('prospect_id')->nullable();
            $table->text('message');
            $table->text('error')->nullable();
            $table->json('media')->nullable();
            $table->string('source')->nullable();
            $table->boolean('from_user')->default(true);
            $table->boolean('is_incoming')->default(false);
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('cloudtalk_message_id', 191)->nullable()->index();
            $table->string('cloudtalk_event_id', 191)->nullable()->index();
            $table->string('cloudtalk_sender_number', 32)->nullable();
            $table->timestamp('cloudtalk_send_started_at')->nullable();
            $table->timestamps();
        });
        $schema->create('interactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->boolean('from_user')->default(true);
            $table->string('source');
            $table->string('status')->nullable();
            $table->string('path')->nullable();
            $table->string('number')->nullable();
            $table->string('from_number')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->json('data')->nullable();
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
        DB::table('user_project')->insert([
            ['project_id' => 1, 'user_id' => 7],
            ['project_id' => 2, 'user_id' => 9],
        ]);

        DB::table('projects')->insert([
            ['id' => 1, 'slug' => 'cloudtalk-test'],
            ['id' => 2, 'slug' => 'other-project'],
        ]);
        DB::table('prospects')->insert([
            ['id' => 1, 'project_id' => 2, 'mobile_phone_number' => '06 12 34 56 78'],
            ['id' => 2, 'project_id' => 1, 'mobile_phone_number' => '06 12 34 56 78'],
        ]);
    }

    protected function tearDown(): void
    {
        DB::purge('cloudtalk_webhook_test');
        parent::tearDown();
    }

    public function test_documented_message_is_saved_for_the_project_prospect_without_sending_it_back(): void
    {
        $this->postJson($this->endpoint(), $this->messagePayload())
            ->assertCreated()->assertJson(['prospect_id' => 2]);

        $sms = Sms::query()->sole();
        $this->assertSame('Bonjour, je confirme.', $sms->message);
        $this->assertSame(2, $sms->prospect_id);
        $this->assertSame('cloudtalk', $sms->source);
        $this->assertTrue($sms->is_incoming);
        $this->assertFalse($sms->from_user);
        $this->assertNull($sms->creator_id);
        $this->assertSame('2026-10-19 08:18:38', $sms->sent_at->format('Y-m-d H:i:s'));
        Notification::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_original_sms_endpoint_and_legacy_payload_remain_supported(): void
    {
        $this->postJson($this->endpoint() . '/sms', [
            'from' => '+33612345678', 'message' => 'Ancien format',
        ])->assertCreated();

        $this->assertSame('Ancien format', Sms::query()->sole()->message);
    }

    public function test_incoming_media_without_text_is_saved_and_displayable_without_sending_it_back(): void
    {
        $payload = $this->messagePayload();
        unset($payload['data']['body']);
        $payload['data']['channel'] = 'mms';
        $payload['data']['media'] = [[
            'url' => 'https://media.example.test/photo.png', 'file_name' => 'photo.png', 'extension' => 'png',
        ]];
        $this->postJson($this->endpoint(), $payload)->assertCreated();

        $sms = Sms::query()->sole();
        $this->assertSame('', $sms->message);
        $this->assertSame($payload['data']['media'][0]['url'], $sms->attachments[0]['url']);
        $this->assertSame('photo.png', $sms->attachments[0]['file_name']);
        $this->assertTrue($sms->is_incoming);
        Notification::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_webhook_rejects_unsafe_media_links(): void
    {
        $payload = $this->messagePayload();
        unset($payload['data']['body']);
        $payload['data']['media'] = [['url' => 'javascript:alert(1)'], ['url' => 'file:///etc/passwd']];
        $this->postJson($this->endpoint(), $payload)->assertOk()->assertJson(['message' => 'ignored']);
        $this->assertSame(0, Sms::query()->count());
    }

    public function test_real_incoming_sms_preserves_multiline_text_and_iso_date(): void
    {
        DB::table('prospects')->where('id', 2)->update([
            'mobile_phone_number' => '06 25 66 35 81',
        ]);
        $message = implode("\n", [
            'Bonjour madame ',
            'Je suis désolé de vous avoir déranger mais la demande d’information n’est d’actualité ',
            'Je vous remercie d’avance ',
            'Je vous souhaite une bonne journée ',
            'Cordialement',
        ]);
        $payload = [
            'event_id' => 'fadf9df8-d92d-8cc9-82de-941d80b80012',
            'type' => 'message.received',
            'version' => 'v1',
            'occurred_at' => '2026-10-19T11:25:02.923Z',
            'company_id' => '365811',
            'data' => [
                'id' => '56030350',
                'conversation_id' => '19706315',
                'direction' => 'inbound',
                'body' => $message,
                'channel' => 'sms',
                'fragments' => 3,
                'created_at' => '2026-10-19T11:25:02.000Z',
                'modified_at' => '2026-10-19T11:25:02.000Z',
                'external_number' => '+33625663581',
                'internal_number' => [
                    'id' => '333427',
                    'number_e164' => '+33644606032',
                    'number_type' => 'mobile',
                    'country' => 'FR',
                ],
            ],
        ];

        $this->postJson($this->endpoint(), $payload)
            ->assertCreated()->assertJson(['prospect_id' => 2]);

        $sms = Sms::query()->sole();
        $this->assertSame($message, $sms->message);
        $this->assertTrue($sms->is_incoming);
        $this->assertFalse($sms->from_user);
        $this->assertSame('2026-10-19 11:25:02', $sms->created_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-19 11:25:02', $sms->sent_at->format('Y-m-d H:i:s'));
        Notification::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_zero_text_and_envelope_timestamp_are_accepted(): void
    {
        $payload = $this->messagePayload();
        $payload['data']['body'] = '0';
        unset($payload['data']['created_at']);
        $this->postJson($this->endpoint(), $payload)->assertCreated();

        $sms = Sms::query()->sole();
        $this->assertSame('0', $sms->message);
        $this->assertSame('2026-10-19 08:18:39', $sms->sent_at->format('Y-m-d H:i:s'));
    }

    public function test_unmatched_or_incomplete_messages_are_ignored(): void
    {
        $payload = $this->messagePayload();
        $payload['data']['external_number'] = '+33199999999';
        $this->postJson($this->endpoint(), $payload)->assertOk()->assertJson(['reason' => 'prospect_not_found']);

        unset($payload['data']['body']);
        $this->postJson($this->endpoint(), $payload)->assertOk()->assertJson(['reason' => 'missing_number_or_message']);
        $this->assertDatabaseCount('sms', 0);
    }

    public function test_other_call_events_are_not_treated_as_messages(): void
    {
        $payload = $this->messagePayload();
        $payload['type'] = 'call.ringing';
        $this->postJson($this->endpoint(), $payload)->assertOk()->assertJson(['reason' => 'unsupported_event']);
        $this->assertDatabaseCount('sms', 0);
    }

    /** @dataProvider callReferences */
    public function test_recording_is_attached_by_call_reference_without_replacing_existing_data(array $reference): void
    {
        $id = $this->interaction(2, array_merge($reference, ['note' => 'Conserver', 'recorded' => false]));
        $payload = $this->recordingPayload();

        $this->postJson($this->endpoint(), $payload)
            ->assertOk()->assertJson(['interaction_id' => $id, 'prospect_id' => 2]);

        $interaction = Interaction::query()->findOrFail($id);
        $this->assertSame('143223459', $interaction->cloudTalkCallId());
        $this->assertSame('Conserver', $interaction->data['note']);
        $this->assertTrue($interaction->data['recorded']);
        $this->assertSame($payload, $interaction->data['cloudtalk_recording']);
        $this->assertSame('answered', $interaction->status);
        $this->assertNull($interaction->path);
        $this->assertNotNull($interaction->audio);
        Http::assertNothingSent();

        // Retries, including on the former SMS URL, update the same interaction.
        $this->postJson($this->endpoint() . '/sms', $payload)->assertOk();
        $this->assertDatabaseCount('interactions', 1);
        $this->assertDatabaseCount('sms', 0);
    }

    public static function callReferences(): array
    {
        return [
            'call id' => [['call_id' => '143223459']],
            'numeric call id' => [['call_id' => 143223459]],
            'uuid' => [['call_uuid' => '018f4a2b-3c4d-4e5f-8a9b-0c1d2e3f4a5b']],
            'iframe id' => [['id' => '018f4a2b-3c4d-4e5f-8a9b-0c1d2e3f4a5b']],
            'normalized history uuid' => [['cloudtalk_call' => ['uuid' => '018f4a2b-3c4d-4e5f-8a9b-0c1d2e3f4a5b']]],
            'raw history id' => [['cloudtalk_history' => ['Cdr' => ['id' => '143223459']]]],
        ];
    }

    public function test_recording_does_not_update_another_project_or_provider_or_create_an_interaction(): void
    {
        $otherProjectId = $this->interaction(1, ['call_id' => '143223459']);
        $otherProviderId = $this->interaction(2, ['call_id' => '143223459'], 'aircall');

        $this->postJson($this->endpoint(), $this->recordingPayload())
            ->assertOk()->assertJson(['reason' => 'interaction_not_found']);

        foreach ([$otherProjectId, $otherProviderId] as $id) {
            $this->assertArrayNotHasKey('recorded', Interaction::query()->findOrFail($id)->data);
        }
        $this->assertDatabaseCount('interactions', 2);
        $this->assertDatabaseCount('sms', 0);

        $payload = $this->recordingPayload();
        unset($payload['data']['call_id']);
        $this->postJson($this->endpoint(), $payload)->assertOk()->assertJson(['reason' => 'missing_call_id']);
    }

    public function test_voicemail_creates_a_prospect_interaction_then_attaches_the_recording(): void
    {
        $ended = $this->endedPayload();
        $response = $this->postJson($this->endpoint(), $ended)
            ->assertCreated()->assertJson(['prospect_id' => 2]);
        $id = $response->json('interaction_id');

        $interaction = Interaction::query()->sole();
        $this->assertSame('voicemail', $interaction->status);
        $this->assertSame('voicemail', $interaction->call_outcome);
        $this->assertSame('incoming', $interaction->call_direction);
        $this->assertFalse($interaction->from_user);
        $this->assertSame('+33612345678', $interaction->number);
        $this->assertSame('+33123456789', $interaction->from_number);
        $this->assertSame('2026-10-19 08:17:30', $interaction->started_at);
        $this->assertSame('2026-10-19 08:18:30', $interaction->ended_at);
        $this->assertSame(60, $interaction->duration);
        $this->assertTrue($interaction->data['is_voicemail']);
        $this->assertSame($ended, $interaction->data['cloudtalk_ended']);
        $this->assertNull($interaction->audio);

        $this->postJson($this->endpoint(), $this->recordingPayload())
            ->assertOk()->assertJson(['interaction_id' => $id]);

        $interaction->refresh();
        $this->assertTrue($interaction->data['recorded']);
        $this->assertSame('voicemail', $interaction->call_outcome);
        $this->assertNotNull($interaction->audio);

        // A retried call.ended keeps the ready recording on the same interaction.
        $this->postJson($this->endpoint(), $ended)->assertOk()->assertJson(['interaction_id' => $id]);
        $interaction->refresh();
        $this->assertTrue($interaction->data['recorded']);
        $this->assertSame($this->recordingPayload(), $interaction->data['cloudtalk_recording']);
        $this->assertDatabaseCount('interactions', 1);
        $this->assertDatabaseCount('sms', 0);
        Http::assertNothingSent();
    }

    public function test_voicemail_reuses_the_existing_interaction_when_recording_arrives_first(): void
    {
        $id = $this->interaction(2, [
            'call_uuid' => $this->recordingPayload()['data']['call_uuid'],
            'note' => 'Conserver',
        ]);
        $this->postJson($this->endpoint(), $this->recordingPayload())->assertOk();
        $this->postJson($this->endpoint() . '/sms', $this->endedPayload())
            ->assertOk()->assertJson(['interaction_id' => $id]);

        $interaction = Interaction::query()->sole();
        $this->assertSame('voicemail', $interaction->call_outcome);
        $this->assertSame('Conserver', $interaction->data['note']);
        $this->assertTrue($interaction->data['recorded']);
        $this->assertNotNull($interaction->audio);
        $this->assertArrayHasKey('cloudtalk_ended', $interaction->data);
        $this->assertArrayHasKey('cloudtalk_recording', $interaction->data);
    }

    public function test_unmatched_voicemail_does_not_create_an_orphan_or_update_another_project(): void
    {
        $id = $this->interaction(1, ['call_id' => '143223459']);
        DB::table('prospects')->where('id', 2)->update(['mobile_phone_number' => null]);

        $this->postJson($this->endpoint(), $this->endedPayload())
            ->assertOk()->assertJson(['reason' => 'prospect_not_found']);
        $this->assertDatabaseCount('interactions', 1);
        $this->assertSame('answered', Interaction::query()->findOrFail($id)->status);

        $payload = $this->endedPayload();
        unset($payload['data']['external_number']);
        $this->postJson($this->endpoint(), $payload)
            ->assertOk()->assertJson(['reason' => 'prospect_not_found']);
    }

    public function test_normal_call_ended_is_not_classified_as_voicemail(): void
    {
        $payload = $this->endedPayload();
        $payload['data']['is_voicemail'] = false;
        $payload['data']['direction'] = 'outgoing';
        $payload['data']['talking_time'] = 45;
        $payload['data']['user'] = ['id' => '42'];
        DB::table('lines')->insert([
            ['project_id' => 2, 'user_id' => 9, 'operator' => 'cloudtalk', 'config' => json_encode(['agent_id' => '42'])],
            ['project_id' => 1, 'user_id' => 7, 'operator' => 'cloudtalk', 'config' => json_encode(['agent_id' => 42])],
        ]);

        $response = $this->postJson($this->endpoint(), $payload)->assertCreated();
        $id = $response->json('interaction_id');
        $interaction = Interaction::query()->sole();
        $this->assertFalse($interaction->data['is_voicemail']);
        $this->assertNull($interaction->call_outcome);
        $this->assertSame('answered', $interaction->status);
        $this->assertSame(7, $interaction->creator_id);
        $this->assertSame('outgoing', $interaction->call_direction);
        $this->assertTrue($interaction->from_user);
        $this->assertSame(2, $interaction->prospect_id);

        $this->postJson($this->endpoint(), $this->recordingPayload())->assertOk();
        $this->postJson($this->endpoint(), $payload)->assertOk()->assertJson(['interaction_id' => $id]);
        $interaction->refresh();
        $this->assertTrue($interaction->data['recorded']);
        $this->assertNotNull($interaction->audio);
        $this->assertDatabaseCount('interactions', 1);

        unset($payload['data']['call_id']);
        $this->postJson($this->endpoint(), $payload)->assertOk()->assertJson(['reason' => 'missing_call_id']);
    }

    public function test_incoming_call_without_talk_time_is_saved_without_assuming_it_was_missed(): void
    {
        $payload = $this->endedPayload();
        $payload['data']['is_voicemail'] = false;
        $this->postJson($this->endpoint(), $payload)->assertCreated()->assertJson(['prospect_id' => 2]);

        $interaction = Interaction::query()->sole();
        $this->assertSame('ended', $interaction->status);
        $this->assertSame('incoming', $interaction->call_direction);
        $this->assertFalse($interaction->from_user);
        $this->assertNull($interaction->call_outcome);
        $this->assertNull($interaction->creator_id);
    }

    public function test_unmatched_normal_call_is_ignored(): void
    {
        $payload = $this->endedPayload();
        $payload['data']['is_voicemail'] = false;
        $payload['data']['external_number'] = '+33199999999';
        $this->postJson($this->endpoint(), $payload)
            ->assertOk()->assertJson(['reason' => 'prospect_not_found']);
        $this->assertDatabaseCount('interactions', 0);
    }

    /** @dataProvider voicemailFlags */
    public function test_official_voicemail_flag_is_recognized_in_stored_call_data(array $data, ?string $expected): void
    {
        $interaction = new Interaction(['source' => 'cloudtalk', 'data' => $data]);
        $this->assertSame($expected, $interaction->call_outcome);
    }

    public static function voicemailFlags(): array
    {
        return [
            'call ended' => [['is_voicemail' => true], 'voicemail'],
            'stored event' => [['cloudtalk_ended' => ['data' => ['is_voicemail' => true]]], 'voicemail'],
            'normalized call' => [['cloudtalk_call' => ['is_voicemail' => true]], 'voicemail'],
            'raw history' => [['cloudtalk_history' => ['Cdr' => ['is_voicemail' => true]]], 'voicemail'],
            'false flag' => [['is_voicemail' => false], null],
        ];
    }

    protected function endpoint(): string
    {
        return '/webhook/project/cloudtalk-test/cloudtalk';
    }

    protected function messagePayload(): array
    {
        return [
            'event_id' => '018f9d21-4e5f-8a6b-9c0d-1e2f3a4b5c6d',
            'type' => 'message.received', 'version' => 'v1', 'company_id' => '123456',
            'occurred_at' => '2026-10-19T08:18:39Z',
            'data' => [
                'id' => '987655', 'conversation_id' => '55321',
                'direction' => 'inbound', 'channel' => 'sms',
                'external_number' => '+33612345678',
                'internal_number' => ['number_e164' => '+33123456789'],
                'body' => 'Bonjour, je confirme.', 'created_at' => '2026-10-19 08:18:38',
            ],
        ];
    }

    protected function recordingPayload(): array
    {
        return [
            'event_id' => '2b4c6d8e-0f1a-8c3e-9d5f-1a2b3c4d5e6f',
            'type' => 'call.recording_ready', 'version' => 'v1', 'company_id' => '123456',
            'occurred_at' => '2026-10-19T08:18:39Z',
            'data' => [
                'call_id' => '143223459',
                'call_uuid' => '018f4a2b-3c4d-4e5f-8a9b-0c1d2e3f4a5b',
                'contacts' => [],
            ],
        ];
    }

    protected function endedPayload(): array
    {
        $payload = $this->recordingPayload();
        $payload['type'] = 'call.ended';
        $payload['event_id'] = '1f3a9c2e-6b4d-8a2f-9c1d-7e5f3a2b4c6d';
        $payload['data'] = array_merge($payload['data'], [
            'direction' => 'incoming',
            'external_number' => '+33612345678',
            'internal_number' => ['number_e164' => '+33123456789'],
            'started_at' => '2026-10-19T08:17:30Z',
            'ended_at' => '2026-10-19T08:18:30Z',
            'recorded' => true,
            'is_voicemail' => true,
        ]);

        return $payload;
    }

    protected function interaction(int $prospectId, array $data, string $source = 'cloudtalk'): int
    {
        return DB::table('interactions')->insertGetId([
            'prospect_id' => $prospectId, 'source' => $source,
            'status' => 'answered', 'data' => json_encode($data),
        ]);
    }
}
