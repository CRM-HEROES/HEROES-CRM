<?php

namespace Tests\Feature;

use App\Models\KavkomCall;
use App\Models\Message;
use App\Models\Sms;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Models\WorkflowNodeExecution;
use App\Services\KavkomService;
use App\Workflows\Actions\ActionRegistry;
use App\Workflows\Providers\ProviderRegistry;
use App\Workflows\VariableResolver;
use App\Workflows\WorkflowExecutionManager;
use App\Workflows\WorkflowService;
use App\Workflows\WorkflowTriggerResolver;
use App\Workflows\WorkflowValidationException;
use App\Workflows\WorkflowValidator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\Support\RecordingHandler;
use Tests\TestCase;

/**
 * Communication actions (email, SMS, call), operators, variables, rate limit.
 * The registry of actions is the real one, as the application builds it.
 */
class WorkflowCommunicationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'workflow_comm_test',
            'database.connections.workflow_comm_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
            'queue.default' => 'sync',
        ]);

        // The model observers (SMS / email sending) are the CRM's, not under test
        Model::unsetEventDispatcher();

        (include base_path('database/migrations/2026_10_30_000001_create_workflow_tables.php'))->up();
        (include base_path('database/migrations/2026_10_30_000002_add_workflow_filter_memberships.php'))->up();
        (include base_path('database/migrations/2026_10_30_000003_add_waiting_event_to_workflow_executions.php'))->up();

        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        $schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
        });
        $schema->create('user_project', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('project_id');
        });
        $schema->create('prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile_phone_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('company_name')->nullable();
            $table->string('city')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        $schema->create('prospect_user', function (Blueprint $table) {
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        $schema->create('project_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->string('key');
            $table->text('value')->nullable();
        });
        $schema->create('lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name');
            $table->string('operator');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('config')->nullable();
            $table->timestamps();
        });
        $schema->create('threads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->boolean('private')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->softDeletes();
        });
        $schema->create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('thread_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->boolean('from_user')->default(false);
            $table->text('body')->nullable();
            $table->text('plain_text')->nullable();
            $table->text('error')->nullable();
            $table->boolean('sent')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->unsignedBigInteger('archived_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        $schema->create('sms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->boolean('from_user')->default(false);
            $table->boolean('is_incoming')->default(false);
            $table->text('message')->nullable();
            $table->string('source')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
        $schema->create('kavkom_calls', function (Blueprint $table) {
            $table->id();
            $table->string('call_uuid')->unique();
            $table->unsignedBigInteger('prospect_id')->nullable();
            $table->unsignedBigInteger('interaction_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('domain_uuid')->nullable();
            $table->string('direction')->nullable();
            $table->string('destination')->nullable();
            $table->string('status')->default('initiated');
            $table->timestamps();
        });

        DB::table('projects')->insert([['id' => 1, 'name' => 'Heroes'], ['id' => 2, 'name' => 'Other']]);
        DB::table('users')->insert(['id' => 7, 'name' => 'Tonny', 'last_name' => 'Andritina', 'email' => 'tonny@example.com']);
        DB::table('prospects')->insert([
            ['id' => 1, 'project_id' => 1, 'first_name' => 'Marie', 'last_name' => 'Curie', 'email' => 'marie@example.com', 'mobile_phone_number' => '0611223344', 'company_name' => 'Radium'],
            ['id' => 2, 'project_id' => 1, 'first_name' => 'Pierre', 'last_name' => 'Curie', 'email' => null, 'mobile_phone_number' => null, 'company_name' => null],
            ['id' => 3, 'project_id' => 1, 'first_name' => 'Irene', 'last_name' => 'Joliot', 'email' => 'irene@example.com', 'mobile_phone_number' => '0655667788', 'company_name' => null],
        ]);
        DB::table('prospect_user')->insert(['prospect_id' => 1, 'user_id' => 7, 'created_at' => now()]);
        DB::table('threads')->insert([
            ['id' => 1, 'project_id' => 1, 'private' => 0],
            ['id' => 2, 'project_id' => 1, 'private' => 1],
            ['id' => 3, 'project_id' => 2, 'private' => 0],
        ]);

        // Operators configured in the project settings
        $this->setting(1, 'smsbox', ['api_key' => 'SECRET-SMSBOX-KEY']);
        $this->setting(1, 'email', ['host' => 'smtp.ovh.net', 'username' => 'crm', 'password' => 'SECRET-SMTP-PASSWORD']);
        $this->setting(2, 'mtarget', ['token' => 'OTHER-PROJECT']);
        DB::table('lines')->insert([
            ['id' => 10, 'project_id' => 1, 'name' => 'Tonny', 'operator' => 'kavkom', 'user_id' => 7,
                'config' => json_encode(['api_token' => 'SECRET-KAVKOM-TOKEN', 'domain_uuid' => 'dom-1', 'phone_number' => '33612345678', 'extension' => '101'])],
            ['id' => 11, 'project_id' => 1, 'name' => 'No token', 'operator' => 'kavkom', 'user_id' => 7, 'config' => json_encode(['phone_number' => '3361'])],
            ['id' => 12, 'project_id' => 1, 'name' => 'Agent', 'operator' => 'cloudtalk', 'user_id' => 7, 'config' => json_encode(['numero' => '+33700000000', 'api_key_secret' => 'SECRET-CT'])],
            ['id' => 13, 'project_id' => 1, 'name' => 'No number', 'operator' => 'cloudtalk', 'user_id' => 7, 'config' => json_encode(['api_key_secret' => 'x'])],
            ['id' => 14, 'project_id' => 2, 'name' => 'Other project', 'operator' => 'kavkom', 'user_id' => 7,
                'config' => json_encode(['api_token' => 'T2', 'domain_uuid' => 'dom-2'])],
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // Helpers

    protected function setting(int $projectId, string $key, array $value): void
    {
        DB::table('project_settings')->insert(['project_id' => $projectId, 'key' => $key, 'value' => json_encode($value)]);
    }

    protected function node(string $id, string $type, array $config = []): array
    {
        return ['id' => $id, 'type' => $type, 'name' => $id, 'config' => $config];
    }

    /**
     * trigger -> action [-> action]
     */
    protected function graph(array ...$actions): array
    {
        $nodes = [$this->node('t', 'trigger', ['trigger' => 'prospect.created'])];
        $edges = [];
        $previous = 't';

        foreach ($actions as $i => $config) {
            $id = 'a' . ($i + 1);
            $nodes[] = $this->node($id, 'action', $config);
            $edges[] = ['from' => $previous, 'to' => $id];
            $previous = $id;
        }

        return ['nodes' => $nodes, 'edges' => $edges];
    }

    protected function activate(array $graph, int $projectId = 1): Workflow
    {
        $workflow = Workflow::create(['project_id' => $projectId, 'name' => 'Test']);
        $service = app(WorkflowService::class);
        $service->publish($workflow, $graph);
        $service->activate($workflow);

        return $workflow->fresh();
    }

    protected function fire(int $prospectId = 1): void
    {
        app(WorkflowTriggerResolver::class)->fire('prospect.created', 1, $prospectId, "prospect.created:$prospectId");
    }

    protected function execution(int $id = 1): WorkflowExecution
    {
        return WorkflowExecution::findOrFail($id);
    }

    protected function nodeRow(string $node, int $executionId = 1): WorkflowNodeExecution
    {
        return WorkflowNodeExecution::where('execution_id', $executionId)->where('node_id', $node)->firstOrFail();
    }

    protected function errors(array $graph, ?int $projectId = 1): array
    {
        return app(WorkflowValidator::class)->validate($graph, $projectId);
    }

    protected function sms(array $overrides = []): array
    {
        return $overrides + ['action' => 'send_sms', 'provider' => 'sms:smsbox', 'message' => 'Bonjour {{prospect.firstname}}'];
    }

    protected function email(array $overrides = []): array
    {
        return $overrides + ['action' => 'send_email', 'provider' => 'email:project', 'thread' => 1, 'body' => '<p>Bonjour {{prospect.firstname}}</p>'];
    }

    // Operators

    public function test_only_the_active_operators_of_the_project_with_the_capability_are_offered(): void
    {
        $registry = app(ProviderRegistry::class);
        $refs = fn (string $capability) => array_column($registry->forProject(1, $capability), 'ref');

        $this->assertSame(['email:project'], $refs('email'));
        $this->assertEqualsCanonicalizing(['sms:smsbox', 'line:12'], $refs('sms')); // cloudtalk with a number only
        $this->assertSame(['line:10'], $refs('call'));                              // kavkom with a token only, this project only

        $this->assertSame(['sms:mtarget'], array_column($registry->forProject(2, 'sms'), 'ref'));
        $this->assertSame(['line:14'], array_column($registry->forProject(2, 'call'), 'ref'));
    }

    public function test_operators_never_expose_credentials(): void
    {
        $registry = app(ProviderRegistry::class);

        $json = json_encode(array_map([ProviderRegistry::class, 'public'], $registry->forProject(1)));

        foreach (['SECRET-SMSBOX-KEY', 'SECRET-SMTP-PASSWORD', 'SECRET-KAVKOM-TOKEN', 'SECRET-CT', 'dom-1'] as $secret) {
            $this->assertStringNotContainsString($secret, $json);
        }
    }

    public function test_an_operator_of_another_project_cannot_be_used(): void
    {
        $registry = app(ProviderRegistry::class);

        $this->assertNull($registry->find(1, 'sms:mtarget', 'sms'));
        $this->assertNull($registry->find(1, 'line:14', 'call'));
        $this->assertNull($registry->find(1, 'line:10', 'sms')); // wrong capability

        $errors = $this->errors($this->graph($this->sms(['provider' => 'sms:mtarget', 'provider_label' => 'MTarget'])));
        $this->assertContains(
            'L\'action "a1" (Envoyer un SMS) utilise l\'opérateur "MTarget", mais il n\'est plus configuré pour ce projet.',
            $errors
        );
    }

    // SMS

    public function test_a_workflow_sends_a_sms_through_the_configured_gateway(): void
    {
        $this->activate($this->graph($this->sms()));

        $this->fire(1);

        $this->assertSame(WorkflowExecution::COMPLETED, $this->execution()->status);
        $sms = Sms::first();
        $this->assertSame('Bonjour Marie', $sms->message);
        $this->assertSame('smsbox', $sms->source);
        $this->assertTrue($sms->from_user);
        $this->assertSame(1, $sms->prospect_id);

        $log = $this->nodeRow('a1');
        $this->assertSame('sms:smsbox', $log->result['provider']);
        $this->assertSame($sms->id, $log->result['sms_id']);
    }

    public function test_a_sms_through_a_cloudtalk_line_is_sent_by_the_agent_of_the_line(): void
    {
        $this->activate($this->graph($this->sms(['provider' => 'line:12'])));

        $this->fire(1);

        $sms = Sms::first();
        $this->assertSame('cloudtalk', $sms->source);
        $this->assertSame(7, $sms->creator_id);
    }

    public function test_a_prospect_without_mobile_makes_the_step_fail_with_a_clear_message(): void
    {
        $this->activate($this->graph($this->sms()));

        $this->fire(2);

        $this->assertSame(WorkflowExecution::FAILED, $this->execution()->status);
        $this->assertSame("Le prospect n'a pas de numéro de mobile.", $this->execution()->error);
        $this->assertSame(0, Sms::count());
    }

    // Email

    public function test_a_workflow_sends_an_email_through_the_discussion_channel(): void
    {
        DB::table('prospects')->where('id', 1)->update(['first_name' => '<b>Marie</b>']);
        $this->activate($this->graph($this->email()));

        $this->fire(1);

        $this->assertSame(WorkflowExecution::COMPLETED, $this->execution()->status);
        $message = Message::first();
        $this->assertSame('<p>Bonjour &lt;b&gt;Marie&lt;/b&gt;</p>', $message->body); // values are escaped in emails
        $this->assertSame(1, $message->thread_id);
        $this->assertTrue((bool) $message->from_user);
        $this->assertSame($message->id, $this->nodeRow('a1')->result['message_id']);
    }

    public function test_a_prospect_without_email_makes_the_step_fail(): void
    {
        $this->activate($this->graph($this->email()));

        $this->fire(2);

        $this->assertSame(WorkflowExecution::FAILED, $this->execution()->status);
        $this->assertSame("Le prospect n'a pas d'adresse email.", $this->execution()->error);
        $this->assertSame(0, Message::count());
    }

    public function test_the_email_channel_must_exist_in_the_project_and_not_be_private(): void
    {
        $this->assertContains(
            'Le canal de discussion de l\'action "a1" est privé : aucun email n\'y est envoyé.',
            $this->errors($this->graph($this->email(['thread' => 2])))
        );
        $this->assertContains(
            'Le canal de discussion de l\'action "a1" n\'existe plus dans ce projet.',
            $this->errors($this->graph($this->email(['thread' => 3]))) // thread of another project
        );
        $this->assertSame([], $this->errors($this->graph($this->email())));
    }

    public function test_without_a_channel_the_email_goes_to_the_first_public_one(): void
    {
        $this->activate($this->graph($this->email(['thread' => null])));

        $this->assertSame([], $this->errors($this->graph($this->email(['thread' => null]))));

        $this->fire(1);

        $this->assertSame(WorkflowExecution::COMPLETED, $this->execution()->status);
        $this->assertSame(1, Message::first()->thread_id); // thread 2 is private
    }

    public function test_without_any_public_channel_the_email_cannot_be_set_up(): void
    {
        DB::table('threads')->where('project_id', 1)->update(['private' => 1]);

        $this->assertCount(1, $this->errors($this->graph($this->email(['thread' => null]))));
    }

    // Call

    protected function mockKavkom(array $result): void
    {
        $this->mock(KavkomService::class, function ($mock) use ($result) {
            $mock->shouldReceive('callFromConfig')
                ->once()
                ->withArgs(fn ($config, $number) => $config['domain_uuid'] === 'dom-1' && $number === '0611223344')
                ->andReturn($result);
            $mock->shouldReceive('linkOutboundCall')->andReturnUsing(function ($result, $prospectId, $userId, $domain, $number) {
                (new KavkomService())->linkOutboundCall($result, $prospectId, $userId, $domain, $number);
            });
        });
    }

    public function test_a_workflow_starts_a_call_through_the_kavkom_line_and_links_it(): void
    {
        $this->mockKavkom(['success' => true, 'call_uuid' => 'uuid-123']);
        $this->activate($this->graph(['action' => 'make_call', 'provider' => 'line:10']));

        $this->fire(1);

        $this->assertSame(WorkflowExecution::COMPLETED, $this->execution()->status);
        $this->assertSame('uuid-123', $this->nodeRow('a1')->result['call_uuid']);

        $call = KavkomCall::first();
        $this->assertSame('uuid-123', $call->call_uuid);
        $this->assertSame(1, $call->prospect_id);
        $this->assertSame(7, $call->user_id);
        $this->assertSame('outbound', $call->direction);
    }

    public function test_a_refused_call_fails_the_step_with_the_message_of_the_telephony_service(): void
    {
        $this->mockKavkom(['success' => false, 'message' => 'Extension indisponible']);
        $this->activate($this->graph(['action' => 'make_call', 'provider' => 'line:10']));

        $this->fire(1);

        $this->assertSame(WorkflowExecution::FAILED, $this->execution()->status);
        $this->assertSame('Extension indisponible', $this->execution()->error);
        $this->assertSame(0, KavkomCall::count());
    }

    public function test_the_softphone_outgoing_number_check_is_shared_with_workflows(): void
    {
        $result = (new KavkomService())->callFromConfig(['phone_number' => '0000000000', 'api_token' => 't', 'domain_uuid' => 'd'], '0611223344');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('numéro sortant Kavkom', $result['message']);
    }

    // Operator removed / error handling

    public function test_an_operator_removed_after_the_activation_fails_the_step_without_using_another(): void
    {
        $this->activate($this->graph($this->sms(['provider_label' => 'SMSBOX'])));
        DB::table('project_settings')->where('key', 'smsbox')->delete();

        $this->fire(1);

        $this->assertSame(WorkflowExecution::FAILED, $this->execution()->status);
        $this->assertSame('L\'opérateur "SMSBOX" n\'est plus configuré pour ce projet : Envoyer un SMS impossible.', $this->execution()->error);
        $this->assertSame(0, Sms::count());
    }

    public function test_on_error_continue_records_the_failure_and_goes_on(): void
    {
        $this->activate($this->graph(
            $this->sms(['on_error' => 'continue']),                                  // prospect 2 has no mobile
            ['action' => 'send_email', 'provider' => 'email:project', 'thread' => 1, 'body' => 'Hello']
        ));
        DB::table('prospects')->where('id', 2)->update(['email' => 'pierre@example.com']);

        $this->fire(2);

        $this->assertSame(WorkflowExecution::COMPLETED, $this->execution()->status);
        $this->assertSame('failed', $this->nodeRow('a1')->status);
        $this->assertSame("Le prospect n'a pas de numéro de mobile.", $this->nodeRow('a1')->error);
        $this->assertSame('completed', $this->nodeRow('a2')->status);
        $this->assertSame(1, Message::count());
    }

    public function test_publishing_and_activating_check_the_operators_with_a_clear_message(): void
    {
        $workflow = Workflow::create(['project_id' => 1, 'name' => 'Test']);
        $service = app(WorkflowService::class);
        $service->publish($workflow, $this->graph($this->sms(['provider_label' => 'SMSBOX'])));

        // The gateway is removed before the activation
        DB::table('project_settings')->where('key', 'smsbox')->delete();

        try {
            $service->activate($workflow);
            $this->fail('The activation must be refused');
        } catch (WorkflowValidationException $e) {
            $this->assertSame(
                ['L\'action "a1" (Envoyer un SMS) utilise l\'opérateur "SMSBOX", mais il n\'est plus configuré pour ce projet.'],
                $e->errors
            );
        }
    }

    public function test_communication_actions_are_validated(): void
    {
        $this->assertContains(
            'L\'action "a1" (Envoyer un SMS) n\'a pas d\'opérateur : choisissez-en un.',
            $this->errors($this->graph($this->sms(['provider' => ''])))
        );
        $this->assertContains(
            'L\'action "a1" (Envoyer un SMS) n\'a pas de message.',
            $this->errors($this->graph($this->sms(['message' => ' '])))
        );
        $this->assertContains(
            'L\'action "a1" utilise une variable inconnue : {{prospect.nom}}.',
            $this->errors($this->graph($this->sms(['message' => 'Bonjour {{prospect.nom}}'])))
        );
        $this->assertContains(
            'L\'action "a1" a un mode d\'erreur invalide.',
            $this->errors($this->graph($this->sms(['on_error' => 'ignore'])))
        );
        $this->assertSame([], $this->errors($this->graph($this->sms(), ['action' => 'make_call', 'provider' => 'line:10'])));

        // Structure only (no project): the environment is not checked
        $this->assertSame([], $this->errors($this->graph($this->sms(['provider' => 'sms:whatever'])), null));
    }

    // Rate limit

    public function test_communications_over_the_rate_limit_are_postponed_not_sent(): void
    {
        config(['workflows.rate_limits.sms' => 2]);
        Carbon::setTestNow('2026-10-30 10:00:00');
        $this->activate($this->graph($this->sms()));

        $this->fire(1);
        $this->fire(3);
        $this->fire(1 + 0); // same prospect, same occurrence: nothing new

        // A third distinct occurrence in the same minute
        app(WorkflowTriggerResolver::class)->fire('prospect.created', 1, 1, 'prospect.created:1:again');

        $this->assertSame(2, Sms::count());
        $postponed = WorkflowExecution::where('status', WorkflowExecution::WAITING)->get();
        $this->assertCount(1, $postponed);

        $execution = $postponed->first();
        $this->assertSame('a1', $execution->current_node_id);
        $this->assertTrue($execution->resume_at->between(now()->addSeconds(15), now()->addSeconds(75)));
        // The step was not started: no log row, nothing half done
        $this->assertSame(0, WorkflowNodeExecution::where('execution_id', $execution->id)->where('node_id', 'a1')->count());

        // A minute later the window is free: the step is run
        Carbon::setTestNow('2026-10-30 10:02:00');
        app(WorkflowExecutionManager::class)->resumeDue(100);

        $this->assertSame(3, Sms::count());
        $this->assertSame(WorkflowExecution::COMPLETED, $execution->fresh()->status);
    }

    public function test_the_rate_limit_is_per_operator_and_can_be_disabled(): void
    {
        config(['workflows.rate_limits.sms' => 1]);
        $this->activate($this->graph($this->sms(['provider' => 'sms:smsbox'])));
        $this->activate($this->graph($this->sms(['provider' => 'line:12'])));

        $this->fire(1); // one SMS per operator within the minute: both go

        $this->assertSame(2, Sms::count());

        config(['workflows.rate_limits.sms' => 0]);
        app(WorkflowTriggerResolver::class)->fire('prospect.created', 1, 1, 'prospect.created:1:again');
        $this->assertSame(4, Sms::count());
    }

    // Variables

    public function test_variables_are_resolved_from_a_fixed_list(): void
    {
        $prospect = \App\Models\Prospect::find(1);
        $resolver = new VariableResolver($prospect);

        $this->assertSame(
            'Bonjour Marie Curie (Radium), votre conseiller est Tonny Andritina - Heroes',
            $resolver->render('Bonjour {{ prospect.fullname }} ({{prospect.company}}), votre conseiller est {{user.fullname}} - {{project.name}}')
        );
    }

    public function test_a_known_variable_without_value_is_empty_and_never_leaks_other_data(): void
    {
        $resolver = new VariableResolver(\App\Models\Prospect::find(2)); // no user, no company

        $this->assertSame('[] [] []', $resolver->render('[{{user.firstname}}] [{{prospect.company}}] [{{prospect.password}}]'));
    }

    public function test_variables_are_escaped_in_html_only(): void
    {
        DB::table('prospects')->where('id', 1)->update(['first_name' => 'A&B <i>']);
        $resolver = new VariableResolver(\App\Models\Prospect::find(1));

        $this->assertSame('A&B <i>', $resolver->render('{{prospect.firstname}}', 'text'));
        $this->assertSame('A&amp;B &lt;i&gt;', $resolver->render('{{prospect.firstname}}', 'html'));
    }

    public function test_unknown_variables_are_listed(): void
    {
        $this->assertSame(
            ['prospect.nom', 'order.total'],
            VariableResolver::unknown('{{prospect.firstname}} {{prospect.nom}} {{ order.total }} {{prospect.nom}}')
        );
    }

    public function test_the_real_registry_offers_the_communication_actions_and_the_legacy_ones(): void
    {
        $registry = app(ActionRegistry::class);

        foreach (['send_email', 'send_sms', 'make_call'] as $type) {
            $this->assertNotNull($registry->get($type));
            $this->assertTrue($registry->has($type, 'prospect'));
        }

        // The campaign actions are reused as they are
        $this->assertTrue($registry->has('prospect-attach-label', 'prospect'));
    }

    public function test_resolving_the_registry_does_not_need_the_test_double(): void
    {
        // Sanity check of the test setup: this file never replaces the registry
        $this->assertInstanceOf(ActionRegistry::class, app(ActionRegistry::class));
        $this->assertSame([], RecordingHandler::$calls);
    }
}
