<?php

namespace Tests\Feature;

use App\Agents\Tools\CallStartTool;
use App\Agents\AgentExecutionContext;
use App\Agents\Tools\CallTransferTool;
use App\Agents\Writes\WriteCapabilities;
use App\Calls\CallEligibility;
use App\Calls\CallCompletion;
use App\Calls\Voice\AiVoiceProvider;
use App\Calls\SmsScheduler;
use App\Calls\Voice\FakeVoiceProvider;
use App\Models\Agent;
use App\Models\AgentRun;
use App\Models\CallAttempt;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\User;
use App\Observers\ProspectCallObserver;
use App\Utils\ProjectSetting;
use App\Workflows\Actions\AgentCallAction;
use App\Workflows\Actions\AgentSmsAction;
use App\Models\WorkflowExecution;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\Support\UsesInMemoryDatabase;
use Tests\TestCase;

class CallEligibilityTest extends TestCase
{
    use UsesInMemoryDatabase;

    protected User $owner;
    protected Project $project;
    protected Agent $agent;
    protected Prospect $prospect;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Http::fake();
        $this->withoutMiddleware(\App\Http\Middleware\UserTrackActivity::class);
        $this->setUpInMemoryDatabase();
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('last_activity')->nullable();
            $table->string('ip_address')->nullable();
        });
        Schema::table('user_project', function (Blueprint $table): void {
            $table->timestamp('last_activity')->nullable();
        });
        Schema::create('prospects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('mobile_phone_number')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('project_settings', function (Blueprint $table): void {
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('key');
            $table->text('value')->nullable();
            $table->unsignedBigInteger('creator_id')->nullable();
        });
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('for');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('labels', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('prospect_label', function (Blueprint $table): void {
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('label_id');
        });
        Schema::create('prospect_user', function (Blueprint $table): void {
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('user_id');
        });
        Schema::create('calendars', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('user_calendar', function (Blueprint $table): void {
            $table->unsignedBigInteger('calendar_id');
            $table->unsignedBigInteger('user_id');
        });
        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('calendar_id');
            $table->unsignedBigInteger('prospect_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('interactions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->string('source')->nullable();
            $table->boolean('from_user')->nullable();
            $table->json('data')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
        Schema::create('sms', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->boolean('from_user')->default(false);
            $table->string('source')->nullable();
            $table->text('message')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
        Schema::create('lines', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('operator');
            $table->json('config')->nullable();
            $table->timestamps();
        });
        Schema::create('fields', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('for');
            $table->boolean('meta')->default(false);
            $table->string('slug');
            $table->softDeletes();
        });
        Schema::create('groups', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('user_group', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('group_id');
        });
        Schema::create('prospect_group', function (Blueprint $table): void {
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('group_id');
        });
        Schema::create('revisions', function (Blueprint $table): void {
            $table->id();
            $table->string('revisionable_type');
            $table->unsignedBigInteger('revisionable_id');
            $table->string('key');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_11_13_000003_add_call_controls_and_attempts.php'))->up();
        config([
            'calls.enabled' => true,
            'calls.provider' => 'fake',
            'calls.work_hours.start' => '08:00',
            'calls.work_hours.end' => '17:00',
            'calls.max_attempts' => 3,
            'calls.retry_delay_minutes' => 240,
            'calls.callback_delay_hours' => 48,
        ]);

        $this->owner = User::find($this->makeUser('Call owner'));
        $this->project = Project::withoutGlobalScopes()->find($this->makeProject('Call project', $this->owner->id));
        DB::table('projects')->where('id', $this->project->id)->update(['calls_enabled' => true]);
        $this->agent = Agent::create([
            'user_id' => $this->owner->id,
            'project_id' => $this->project->id,
            'name' => 'Call agent',
            'model' => 'claude-opus-5-5',
            'effort' => 'medium',
            'autonomy' => Agent::PROPOSE,
            'status' => Agent::ACTIVE,
        ]);
        DB::table('projects')->where('id', $this->project->id)->update(['call_agent_id' => $this->agent->id]);
        $this->project->refresh();
        $this->prospect = Prospect::withoutEvents(fn () => Prospect::withoutGlobalScopes()->create([
            'project_id' => $this->project->id,
            'creator_id' => $this->owner->id,
            'first_name' => 'Test',
            'phone_number' => '+33123456789',
            'mobile_phone_number' => '+33123456789',
            'call_consent' => true,
            'do_not_call' => false,
        ]));
    }

    protected function tearDown(): void
    {
        \Carbon\Carbon::setTestNow();
        \Carbon\CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_calls_without_explicit_consent_are_skipped(): void
    {
        DB::table('prospects')->where('id', $this->prospect->id)->update(['call_consent' => null]);
        $this->prospect->refresh();

        $attempt = app(CallEligibility::class)->prepare($this->prospect, $this->agent, $this->project);

        $this->assertSame(CallAttempt::SKIPPED, $attempt->status);
        $this->assertSame('no_consent', $attempt->skip_reason);
    }

    public function test_global_kill_switch_blocks_calls_and_sms(): void
    {
        DB::table('project_settings')->insert([
            'project_id' => null,
            'key' => 'calls.global_enabled',
            'value' => 'false',
        ]);

        $attempt = app(CallEligibility::class)->prepare($this->prospect, $this->agent, $this->project);

        $this->assertSame('global_disabled', $attempt->skip_reason);
        $this->assertSame('global_disabled', app(CallEligibility::class)->checkSms($this->prospect, $this->project));
    }

    public function test_project_activation_is_read_from_the_database_on_every_check(): void
    {
        DB::table('projects')->where('id', $this->project->id)->update(['calls_enabled' => false]);

        $this->assertSame('project_disabled', app(CallEligibility::class)->checkSms($this->prospect, $this->project));
    }

    public function test_provider_fallback_and_project_override_are_used_for_cloudtalk(): void
    {
        config(['calls.provider' => 'fake']);
        ProjectSetting::set($this->project, 'calls', ['provider' => 'cloudtalk']);

        $this->assertSame('cloudtalk', \App\Calls\CallSettings::provider($this->project));
        $this->assertSame(
            'cloudtalk_line_missing',
            app(CallEligibility::class)->checkSms($this->prospect, $this->project, null, $this->owner->id, 'cloudtalk')
        );

        ProjectSetting::set($this->project, 'calls', []);
        $this->assertSame(
            'provider_unconfigured',
            app(CallEligibility::class)->checkSms($this->prospect, $this->project, null, $this->owner->id, 'cloudtalk')
        );
    }

    public function test_cloudtalk_sms_eligibility_rejects_a_sender_equal_to_the_prospect_mobile(): void
    {
        \Carbon\Carbon::setTestNow('2026-03-10 10:00:00');
        \Carbon\CarbonImmutable::setTestNow('2026-03-10 10:00:00');
        ProjectSetting::set($this->project, 'calls', ['provider' => 'cloudtalk']);
        DB::table('lines')->insert([
            'project_id' => $this->project->id,
            'user_id' => $this->owner->id,
            'name' => 'CloudTalk SMS',
            'operator' => 'cloudtalk',
            'config' => json_encode([
                'numero' => $this->prospect->mobile_phone_number,
                'api_key_id' => 'test',
                'api_key_secret' => 'secret',
            ]),
        ]);

        $eligibility = app(CallEligibility::class);
        $this->assertSame(
            'sender_equals_recipient',
            $eligibility->checkSms($this->prospect, $this->project, null, $this->owner->id, 'cloudtalk')
        );
        $checks = $eligibility->smsChecks($this->prospect, $this->project, null, $this->owner->id, 'cloudtalk');
        $senderCheck = collect($checks)->firstWhere('key', 'sender_equals_recipient');
        $this->assertFalse($senderCheck['passed']);
        $this->assertStringContainsString('identique au mobile du prospect', $senderCheck['message']);
    }

    public function test_agent_sms_and_call_actions_refuse_missing_consent_with_distinct_reasons(): void
    {
        \Carbon\Carbon::setTestNow('2026-03-10 10:00:00');
        \Carbon\CarbonImmutable::setTestNow('2026-03-10 10:00:00');
        ProjectSetting::set($this->project, 'calls', ['provider' => 'cloudtalk']);
        $lineId = DB::table('lines')->insertGetId([
            'project_id' => $this->project->id,
            'user_id' => $this->owner->id,
            'name' => 'CloudTalk SMS',
            'operator' => 'cloudtalk',
            'config' => json_encode([
                'numero' => '+33111111111',
                'api_key_id' => 'test',
                'api_key_secret' => 'secret',
            ]),
        ]);
        DB::table('prospects')->where('id', $this->prospect->id)->update(['call_consent' => null]);
        $this->prospect->refresh();
        $execution = new WorkflowExecution();
        $execution->project_id = $this->project->id;

        try {
            app(AgentSmsAction::class)->handle($this->prospect, [
                'agent_id' => $this->agent->id,
                'provider' => 'line:' . $lineId,
                'instruction' => 'Envoyer le message préparé.',
            ], $execution);
            $this->fail('L’action SMS agent doit refuser le prospect sans consentement.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('[no_consent]', $exception->getMessage());
        }

        try {
            app(AgentCallAction::class)->handle($this->prospect, [
                'agent_id' => $this->agent->id,
                'instruction' => 'Appeler le prospect.',
            ], $execution);
            $this->fail('L’action d’appel agent doit refuser le prospect sans consentement.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('[no_consent]', $exception->getMessage());
        }

        $this->assertSame(0, DB::table('sms')->count());
        $this->assertSame('no_consent', CallAttempt::query()->latest('id')->value('skip_reason'));
        Http::assertNothingSent();
    }

    public function test_agent_sms_send_tool_refuses_missing_consent_before_calling_the_manual_route(): void
    {
        \Carbon\Carbon::setTestNow('2026-03-10 10:00:00');
        \Carbon\CarbonImmutable::setTestNow('2026-03-10 10:00:00');
        ProjectSetting::set($this->project, 'smsbox', ['api_key' => 'fake']);
        DB::table('prospects')->where('id', $this->prospect->id)->update(['call_consent' => null]);
        $this->prospect->refresh();
        $context = new AgentExecutionContext($this->owner, $this->project);
        $tool = collect(app(WriteCapabilities::class)->tools())
            ->first(fn ($candidate) => $candidate->key() === 'sms_send');

        $result = $tool->handle($context, [
            'prospect_id' => $this->prospect->id,
            'provider' => 'sms:smsbox',
            'message' => 'Message agent',
        ]);

        $this->assertTrue($result->isError);
        $this->assertSame('VALIDATION_FAILED', $result->code);
        $this->assertStringContainsString('[no_consent]', $result->summary);
        $this->assertSame(0, DB::table('sms')->count());
        Http::assertNothingSent();
    }

    /**
     * @dataProvider smsRefusalCases
     */
    public function test_sms_refusal_messages_have_distinct_machine_reasons(string $reason, string $message): void
    {
        config(['queue.default' => 'sync', 'queue.connections.sync.driver' => 'sync']);
        \Carbon\Carbon::setTestNow('2026-03-10 10:00:00');
        \Carbon\CarbonImmutable::setTestNow('2026-03-10 10:00:00');
        switch ($reason) {
            case 'global_disabled':
                DB::table('project_settings')->insert([
                'project_id' => null,
                'key' => 'calls.global_enabled',
                'value' => 'false',
                ]);
                break;
            case 'project_disabled':
                DB::table('projects')->where('id', $this->project->id)->update(['calls_enabled' => false]);
                break;
            case 'no_consent':
                DB::table('prospects')->where('id', $this->prospect->id)->update(['call_consent' => null]);
                break;
            case 'consent_declined':
                DB::table('prospects')->where('id', $this->prospect->id)->update(['call_consent' => false]);
                break;
            case 'do_not_call':
                DB::table('prospects')->where('id', $this->prospect->id)->update(['do_not_call' => true]);
                break;
            case 'outside_calling_hours':
                \Carbon\Carbon::setTestNow('2026-03-14 10:00:00');
                \Carbon\CarbonImmutable::setTestNow('2026-03-14 10:00:00');
                ProjectSetting::set($this->project, 'calls', ['timezone' => 'Europe/Paris']);
                break;
            case 'sms_frequency_limit':
                DB::table('sms')->insert([
                'prospect_id' => $this->prospect->id,
                'creator_id' => $this->owner->id,
                'from_user' => true,
                'source' => 'smsbox',
                'message' => 'Déjà envoyé',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
                ]);
                break;
            case 'invalid_phone':
                DB::table('prospects')->where('id', $this->prospect->id)->update([
                    'phone_number' => '12',
                    'mobile_phone_number' => null,
                ]);
                break;
        }

        if (in_array($reason, ['no_consent', 'consent_declined', 'do_not_call', 'invalid_phone'], true)) {
            $this->prospect->refresh();
        }

        try {
            app(SmsScheduler::class)->sendOrSchedule(
                $this->prospect,
                $this->project,
                $this->owner->id,
                'smsbox',
                'Test'
            );
            $this->fail("Le SMS aurait dû être refusé avec $reason.");
        } catch (\App\Calls\SmsEligibilityException $exception) {
            $this->assertSame($reason, $exception->reason);
            $this->assertStringContainsString($message, $exception->getMessage());
        }
    }

    public static function smsRefusalCases(): array
    {
        return [
            'global kill switch' => ['global_disabled', 'Les appels et messages sont suspendus'],
            'project disabled' => ['project_disabled', 'ne sont pas activés pour ce projet'],
            'missing consent' => ['no_consent', 'consentement explicite'],
            'explicit consent refusal' => ['consent_declined', 'explicitement refusé'],
            'contact refusal' => ['do_not_call', 'demandé à ne plus être contacté'],
            'outside working hours' => ['outside_calling_hours', 'Prochaine ouverture : 16/03/2026 à 08:00 (Europe/Paris)'],
            '48 hour frequency' => ['sms_frequency_limit', '48 dernières heures'],
            'invalid number' => ['invalid_phone', 'numéro du prospect est absent ou invalide'],
        ];
    }

    public function test_cloudtalk_line_absence_and_provider_configuration_have_separate_messages(): void
    {
        config(['queue.default' => 'sync', 'queue.connections.sync.driver' => 'sync']);
        config(['calls.provider' => 'cloudtalk']);
        ProjectSetting::set($this->project, 'calls', ['provider' => 'cloudtalk']);

        try {
            app(SmsScheduler::class)->sendOrSchedule($this->prospect, $this->project, $this->owner->id, 'cloudtalk', 'Test');
            $this->fail('Une ligne CloudTalk absente doit refuser le SMS.');
        } catch (\App\Calls\SmsEligibilityException $exception) {
            $this->assertSame('cloudtalk_line_missing', $exception->reason);
            $this->assertStringContainsString('Aucune ligne CloudTalk SMS', $exception->getMessage());
        }

        DB::table('lines')->insert([
            'project_id' => $this->project->id,
            'user_id' => $this->owner->id,
            'name' => 'CloudTalk sans identifiants',
            'operator' => 'cloudtalk',
            'config' => json_encode(['numero' => '+33111111111']),
        ]);

        try {
            app(SmsScheduler::class)->sendOrSchedule($this->prospect, $this->project, $this->owner->id, 'cloudtalk', 'Test');
            $this->fail('Une ligne CloudTalk incomplètement configurée doit refuser le SMS.');
        } catch (\App\Calls\SmsEligibilityException $exception) {
            $this->assertSame('provider_unconfigured', $exception->reason);
            $this->assertStringContainsString('fournisseur SMS n’est pas configuré', $exception->getMessage());
        }
    }

    public function test_out_of_hours_agent_sms_is_refused_with_next_opening_and_timezone(): void
    {
        \Carbon\Carbon::setTestNow('2026-03-14 10:00:00');
        \Carbon\CarbonImmutable::setTestNow('2026-03-14 10:00:00');
        config([
            'queue.default' => 'database',
            'queue.connections.database.driver' => 'database',
        ]);
        Queue::fake();
        ProjectSetting::set($this->project, 'calls', ['timezone' => 'Europe/Paris']);

        try {
            app(SmsScheduler::class)->sendOrSchedule(
                $this->prospect,
                $this->project,
                $this->owner->id,
                'smsbox',
                'Message de test'
            );
            $this->fail('Un SMS agent hors horaires doit être refusé.');
        } catch (\App\Calls\SmsEligibilityException $exception) {
            $this->assertSame('outside_calling_hours', $exception->reason);
            $this->assertStringContainsString('16/03/2026 à 08:00 (Europe/Paris)', $exception->getMessage());
        }

        Queue::assertNotPushed(\App\Jobs\Agent\SendScheduledSms::class);
        $this->assertSame(0, DB::table('sms')->count());
    }

    public function test_sms_is_accepted_and_cloudtalk_request_is_faked_and_verified(): void
    {
        \Carbon\Carbon::setTestNow('2026-03-10 10:00:00');
        \Carbon\CarbonImmutable::setTestNow('2026-03-10 10:00:00');
        config(['calls.provider' => 'fake']);
        ProjectSetting::set($this->project, 'calls', ['provider' => 'cloudtalk', 'timezone' => 'UTC']);
        $lineId = DB::table('lines')->insertGetId([
            'project_id' => $this->project->id,
            'user_id' => $this->owner->id,
            'name' => 'CloudTalk test',
            'operator' => 'cloudtalk',
            'config' => json_encode([
                'api_key_id' => 'test-id',
                'api_key_secret' => 'test-secret',
                'numero' => '+33111111111',
            ]),
        ]);
        Http::fake([
            'my.cloudtalk.io/api/sms/send.json' => Http::response([
                'responseData' => ['status' => 200, 'success' => true],
            ]),
        ]);
        $line = \App\Models\Line::findOrFail($lineId);

        $result = app(SmsScheduler::class)->sendOrSchedule(
            $this->prospect,
            $this->project,
            $this->owner->id,
            'cloudtalk',
            'Message de test',
            function () use ($line) {
                app(\App\Services\CloudTalk::class)->sendSms(
                    $line,
                    $this->prospect->phone_number,
                    'Message de test'
                );

                return 'accepted';
            }
        );

        $this->assertSame('accepted', $result['sms']);
        Http::assertSent(fn ($request) => $request->url() === 'https://my.cloudtalk.io/api/sms/send.json'
            && $request->data()['recipient'] === '+33123456789'
            && $request->data()['sender'] === '+33111111111'
            && $request->data()['message'] === 'Message de test');
    }

    public function test_dedicated_call_consent_api_persists_values_and_enforces_do_not_call(): void
    {
        \Carbon\Carbon::setTestNow('2026-03-10 10:00:00');
        \Carbon\CarbonImmutable::setTestNow('2026-03-10 10:00:00');
        Queue::fake();
        Notification::fake();
        $url = "/api/project/{$this->project->slug}/prospect/{$this->prospect->id}/call-consent";

        $this->actingAs($this->owner, 'sanctum')
            ->patchJson($url, ['call_consent' => true, 'do_not_call' => false])
            ->assertForbidden();
        $this->setProjectPermission('prospectUpdate');

        $this->patchJson($url, ['call_consent' => true, 'do_not_call' => false])
            ->assertOk()
            ->assertJsonPath('call_consent', true)
            ->assertJsonPath('do_not_call', false);
        $this->assertTrue($this->prospect->fresh()->call_consent);
        $this->assertNull(app(CallEligibility::class)->checkSms($this->prospect->fresh(), $this->project));

        $this->patchJson($url, ['call_consent' => false, 'do_not_call' => false])
            ->assertOk()
            ->assertJsonPath('call_consent', false);

        $this->patchJson($url, ['call_consent' => true, 'do_not_call' => true])
            ->assertOk()
            ->assertJsonPath('call_consent', false)
            ->assertJsonPath('do_not_call', true);
        $this->assertFalse($this->prospect->fresh()->call_consent);
        $this->assertTrue($this->prospect->fresh()->do_not_call);

        $this->patchJson($url, ['call_consent' => 'yes', 'do_not_call' => false])
            ->assertUnprocessable();
    }

    public function test_manual_sms_api_ignores_automated_eligibility_controls(): void
    {
        Queue::fake();
        Notification::fake();
        $this->setProjectPermission('prospectSmsAdd');
        ProjectSetting::set($this->project, 'calls', ['provider' => 'cloudtalk']);
        DB::table('lines')->insert([
            'project_id' => $this->project->id,
            'user_id' => $this->owner->id,
            'name' => 'CloudTalk SMS',
            'operator' => 'cloudtalk',
            'config' => json_encode([
                'numero' => '+33111111111',
                'api_key_id' => 'test',
                'api_key_secret' => 'secret',
            ]),
        ]);
        DB::table('project_settings')->insert([
            'project_id' => null,
            'key' => 'calls.global_enabled',
            'value' => 'false',
        ]);
        DB::table('projects')->where('id', $this->project->id)->update(['calls_enabled' => false]);
        DB::table('prospects')->where('id', $this->prospect->id)->update([
            'call_consent' => false,
            'do_not_call' => true,
        ]);
        DB::table('sms')->insert([
            'prospect_id' => $this->prospect->id,
            'creator_id' => $this->owner->id,
            'from_user' => true,
            'source' => 'cloudtalk',
            'message' => 'Message récent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \Carbon\Carbon::setTestNow('2026-03-14 20:30:00');
        \Carbon\CarbonImmutable::setTestNow('2026-03-14 20:30:00');

        $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/project/{$this->project->slug}/prospect/{$this->prospect->id}/sms", [
                'source' => 'cloudtalk',
                'message' => 'Envoi manuel',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Envoi manuel')
            ->assertJsonPath('source', 'cloudtalk')
            ->assertJsonPath('from_user', true);

        $this->assertSame(2, DB::table('sms')->where('prospect_id', $this->prospect->id)->count());
        Http::assertNothingSent();
    }

    public function test_call_settings_diagnostic_is_admin_only_and_never_sends(): void
    {
        Queue::fake();
        $this->setProjectPermission('projectRoleUpdate');

        $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/project/{$this->project->slug}/setting/calls/diagnostic", [
                'prospect_id' => $this->prospect->id,
            ])
            ->assertOk()
            ->assertJsonPath('prospect_id', $this->prospect->id)
            ->assertJsonPath('checks.0.key', 'global')
            ->assertJsonPath('checks.1.key', 'project')
            ->assertJsonPath('checks.2.key', 'provider')
            ->assertJsonPath('checks.8.key', 'phone');

        Http::assertNothingSent();
    }

    protected function setProjectPermission(string $permissionName): void
    {
        $permission = \Spatie\Permission\Models\Permission::create([
            'name' => $permissionName,
            'guard_name' => 'web',
        ]);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($this->project->id);
        $this->owner->givePermissionTo($permission);
    }

    public function test_calls_outside_local_calling_hours_are_skipped_without_rescheduling(): void
    {
        \Carbon\CarbonImmutable::setTestNow('2026-03-10 20:30:00');

        $attempt = app(CallEligibility::class)->prepare($this->prospect, $this->agent, $this->project);

        $this->assertSame('outside_calling_hours', $attempt->skip_reason);
        $this->assertNull($attempt->scheduled_at);
    }

    public function test_sms_refuses_weekends_consent_and_do_not_call_and_does_not_send_synchronously(): void
    {
        $at = \Carbon\CarbonImmutable::parse('2026-03-14 10:00:00');
        \Carbon\CarbonImmutable::setTestNow($at);
        \Carbon\Carbon::setTestNow($at);
        DB::table('prospects')->where('id', $this->prospect->id)->update(['call_consent' => null]);
        $this->prospect->refresh();
        $this->assertSame('no_consent', app(CallEligibility::class)->checkSms($this->prospect, $this->project, $at));

        DB::table('prospects')->where('id', $this->prospect->id)->update(['call_consent' => true, 'do_not_call' => true]);
        $this->prospect->refresh();
        $this->assertSame('do_not_call', app(CallEligibility::class)->checkSms($this->prospect, $this->project, $at));

        DB::table('prospects')->where('id', $this->prospect->id)->update(['do_not_call' => false]);
        $this->prospect->refresh();
        $this->assertSame('outside_calling_hours', app(CallEligibility::class)->checkSms($this->prospect, $this->project, $at));
        config(['queue.default' => 'sync', 'queue.connections.sync.driver' => 'sync']);

        try {
            app(SmsScheduler::class)->sendOrSchedule($this->prospect, $this->project, $this->owner->id, 'smsbox', 'Message test');
            $this->fail('Aucun SMS ne doit être envoyé hors des heures ouvrées en file synchrone.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Prochaine ouverture', $exception->getMessage());
        }

        $this->assertSame(0, DB::table('sms')->count());
    }

    public function test_project_holiday_moves_the_next_opening_to_the_next_workday(): void
    {
        \Carbon\CarbonImmutable::setTestNow('2026-03-13 18:00:00');
        \Carbon\Carbon::setTestNow('2026-03-13 18:00:00');
        ProjectSetting::set($this->project, 'calls', ['holidays' => ['2026-03-16']]);

        $attempt = app(CallEligibility::class)->prepare($this->prospect, $this->agent, $this->project);

        $this->assertNull($attempt->scheduled_at);
        $this->assertSame('2026-03-17 08:00:00', app(CallEligibility::class)->nextOpening($this->prospect)->format('Y-m-d H:i:s'));
    }

    public function test_do_not_call_and_a_call_today_prevent_a_second_attempt(): void
    {
        \Carbon\CarbonImmutable::setTestNow('2026-03-10 10:00:00');
        \Carbon\Carbon::setTestNow('2026-03-10 10:00:00');
        DB::table('prospects')->where('id', $this->prospect->id)->update(['do_not_call' => true]);
        $this->prospect->refresh();
        $blocked = app(CallEligibility::class)->prepare($this->prospect, $this->agent, $this->project);
        $this->assertSame('do_not_call', $blocked->skip_reason);

        DB::table('prospects')->where('id', $this->prospect->id)->update(['do_not_call' => false]);
        $this->prospect->refresh();
        $called = CallAttempt::create([
            'prospect_id' => $this->prospect->id,
            'agent_id' => $this->agent->id,
            'project_id' => $this->project->id,
            'provider' => 'fake',
            'status' => CallAttempt::COMPLETED,
            'started_at' => now(),
        ]);
        $second = app(CallEligibility::class)->prepare($this->prospect, $this->agent, $this->project);
        $this->assertSame('already_called_today', $second->skip_reason);
        $this->assertNotNull($called->id);
    }

    public function test_maximum_attempts_and_recent_solicitation_are_enforced(): void
    {
        \Carbon\CarbonImmutable::setTestNow('2026-03-10 10:00:00');
        \Carbon\Carbon::setTestNow('2026-03-10 10:00:00');
        for ($i = 1; $i <= 3; $i++) {
            CallAttempt::create([
                'prospect_id' => $this->prospect->id,
                'agent_id' => $this->agent->id,
                'project_id' => $this->project->id,
                'provider' => 'fake',
                'status' => CallAttempt::COMPLETED,
                'started_at' => now()->subDays(3)->subHours($i),
            ]);
        }
        $maxed = app(CallEligibility::class)->prepare($this->prospect, $this->agent, $this->project);
        $this->assertSame('max_attempts_reached', $maxed->skip_reason);

        CallAttempt::query()->delete();
        CallAttempt::create([
            'prospect_id' => $this->prospect->id,
            'agent_id' => $this->agent->id,
            'project_id' => $this->project->id,
            'provider' => 'fake',
            'status' => CallAttempt::COMPLETED,
            'started_at' => now()->subDay(),
        ]);
        $recent = app(CallEligibility::class)->prepare($this->prospect, $this->agent, $this->project);
        $this->assertSame('solicited_recently', $recent->skip_reason);
    }

    public function test_interested_outcome_is_not_marked_transferred_before_a_transfer_occurs(): void
    {
        config(['calls.fake_outcome' => 'interested']);
        DB::table('projects')->where('id', $this->project->id)->update(['call_transfer_user_id' => $this->owner->id]);
        $this->project->refresh();
        $attempt = CallAttempt::create([
            'prospect_id' => $this->prospect->id,
            'agent_id' => $this->agent->id,
            'project_id' => $this->project->id,
            'provider' => 'fake',
            'status' => CallAttempt::CALLING,
            'started_at' => now(),
        ]);

        $result = app(FakeVoiceProvider::class)->start($attempt, $this->prospect);
        \App\Models\Event::withoutEvents(fn () => app(CallCompletion::class)->complete(
            $attempt,
            $this->prospect,
            $this->project,
            $this->owner,
            $result
        ));

        $this->assertSame(CallAttempt::COMPLETED, $attempt->fresh()->status);
        $this->assertSame('interested', $attempt->fresh()->outcome);
        $this->assertStringStartsWith('fake-', $attempt->fresh()->external_id);
    }

    public function test_ai_voice_skeleton_requires_disclosure_and_never_claims_to_dial(): void
    {
        $attempt = CallAttempt::create([
            'prospect_id' => $this->prospect->id,
            'agent_id' => $this->agent->id,
            'project_id' => $this->project->id,
            'provider' => 'ai_voice',
            'status' => CallAttempt::CALLING,
            'started_at' => now(),
        ]);

        try {
            app(AiVoiceProvider::class)->start($attempt, $this->prospect, ['points_a_dire' => ['Service cité dans le document']]);
            $this->fail('Le squelette ne doit pas prétendre avoir lancé un appel réel.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('aucun appel n’a été lancé', $exception->getMessage());
        }

        $script = json_decode((string) $attempt->fresh()->summary, true)['ai_voice_script'];
        $this->assertStringContainsString('assistant virtuel', $script);
        $this->assertStringContainsString('Service cité dans le document', $script);
        Http::assertNothingSent();
    }

    public function test_signed_voice_end_webhook_completes_the_call_attempt(): void
    {
        $secret = 'voice-webhook-test-secret';
        ProjectSetting::set($this->project, 'calls', ['ai_voice_webhook_secret' => $secret]);
        $attempt = CallAttempt::create([
            'prospect_id' => $this->prospect->id,
            'agent_id' => $this->agent->id,
            'project_id' => $this->project->id,
            'provider' => 'ai_voice',
            'external_id' => 'voice-session-1',
            'status' => CallAttempt::CALLING,
            'started_at' => now(),
        ]);
        $payload = ['outcome' => 'answered', 'duration_seconds' => 42, 'summary' => 'Le prospect a répondu.'];
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $signature = hash_hmac('sha256', $body, $secret);

        $this->call('POST', '/api/voice/' . $attempt->id . '/ended', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_VOICE_SIGNATURE' => $signature,
        ], $body)->assertOk();

        $this->assertSame(CallAttempt::COMPLETED, $attempt->fresh()->status);
        $this->assertSame('answered', $attempt->fresh()->outcome);
        $this->assertSame(42, $attempt->fresh()->duration_seconds);
        $this->assertDatabaseHas('interactions', ['prospect_id' => $this->prospect->id, 'status' => 'answered']);
        Http::assertNothingSent();
    }

    public function test_unavailable_transfer_adds_recall_label_and_schedules_the_next_opening(): void
    {
        $categoryId = DB::table('categories')->insertGetId([
            'project_id' => $this->project->id,
            'for' => 'prospect',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $labelId = DB::table('labels')->insertGetId([
            'category_id' => $categoryId,
            'name' => 'À rappeler',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('projects')->where('id', $this->project->id)->update([
            'call_back_label_id' => $labelId,
            'call_transfer_user_id' => $this->owner->id,
        ]);
        ProjectSetting::set($this->project, 'calls', ['transfer_available' => false]);
        $calendarId = DB::table('calendars')->insertGetId([
            'project_id' => $this->project->id,
            'name' => 'Rappels',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $run = AgentRun::create([
            'agent_id' => $this->agent->id,
            'user_id' => $this->owner->id,
            'project_id' => $this->project->id,
            'status' => AgentRun::COMPLETED,
            'model' => $this->agent->model,
        ]);
        $attempt = CallAttempt::create([
            'prospect_id' => $this->prospect->id,
            'agent_id' => $this->agent->id,
            'project_id' => $this->project->id,
            'agent_run_id' => $run->id,
            'provider' => 'fake',
            'external_id' => 'fake-interested-1',
            'status' => CallAttempt::COMPLETED,
            'outcome' => 'interested',
            'started_at' => now(),
            'ended_at' => now(),
        ]);
        $context = new AgentExecutionContext($this->owner, $this->project);
        $context->agentId = $this->agent->id;
        $context->runId = $run->id;

        $result = \Illuminate\Database\Eloquent\Model::withoutEvents(
            fn () => app(CallTransferTool::class)->handle($context, [])
        );

        $this->assertFalse($result->isError, $result->summary);
        $this->assertSame('callback', $attempt->fresh()->outcome);
        $this->assertSame(CallAttempt::COMPLETED, $attempt->fresh()->status);
        $this->assertDatabaseHas('prospect_label', ['prospect_id' => $this->prospect->id, 'label_id' => $labelId]);
        $this->assertDatabaseHas('events', [
            'calendar_id' => $calendarId,
            'prospect_id' => $this->prospect->id,
            'user_id' => $this->owner->id,
            'name' => 'Rappel demandé',
        ]);
        $this->assertFalse($result->isError);
    }

    public function test_no_answer_adds_the_project_recall_label_and_creates_a_callback_event(): void
    {
        $categoryId = DB::table('categories')->insertGetId([
            'project_id' => $this->project->id,
            'for' => 'prospect',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $labelId = DB::table('labels')->insertGetId([
            'category_id' => $categoryId,
            'name' => 'À rappeler',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('calendars')->insert([
            'project_id' => $this->project->id,
            'name' => 'Principal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('projects')->where('id', $this->project->id)->update(['call_back_label_id' => $labelId]);
        $this->project->refresh();
        $attempt = CallAttempt::create([
            'prospect_id' => $this->prospect->id,
            'agent_id' => $this->agent->id,
            'project_id' => $this->project->id,
            'provider' => 'fake',
            'status' => CallAttempt::CALLING,
            'started_at' => now(),
        ]);

        $result = app(FakeVoiceProvider::class)->start($attempt, $this->prospect);
        \App\Models\Event::withoutEvents(fn () => app(CallCompletion::class)->complete(
            $attempt,
            $this->prospect,
            $this->project,
            $this->owner,
            $result
        ));

        $this->assertDatabaseHas('prospect_label', ['prospect_id' => $this->prospect->id, 'label_id' => $labelId]);
        $this->assertDatabaseHas('events', ['prospect_id' => $this->prospect->id, 'name' => 'Rappel demandé']);
    }

    public function test_prospect_observer_does_not_queue_when_calls_are_disabled(): void
    {
        Queue::fake();
        config(['calls.enabled' => false]);

        app(ProspectCallObserver::class)->created($this->prospect);

        Queue::assertNothingPushed();
    }

    public function test_prospect_observer_keeps_automatic_calls_when_no_agent_call_workflow_exists(): void
    {
        Queue::fake();

        (new ProspectCallObserver())->created($this->prospect);

        Queue::assertPushed(\App\Jobs\PrepareAutomaticCall::class, 1);
    }

    public function test_call_tool_requires_human_approval(): void
    {
        $tool = app(CallStartTool::class);

        $this->assertSame('communication', $tool->risk());
    }
}
