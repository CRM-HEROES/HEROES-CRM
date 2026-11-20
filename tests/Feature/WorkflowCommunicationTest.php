<?php

namespace Tests\Feature;

use App\Jobs\ProspectThreadMessage;
use App\Models\KavkomCall;
use App\Models\Prospect;
use App\Observers\MessageObserver;
use App\Workflows\Actions\EmailHtml;
use App\Workflows\Mailing\EventPlan;
use App\Workflows\Mailing\KitImporter;
use App\Workflows\Mailing\MailingRules;
use App\Workflows\Mailing\PlanBuilder;
use App\Workflows\Mailing\PlanGenerator;
use Illuminate\Support\Facades\URL;
use App\Workflows\Actions\EmailDesign;
use App\Observers\ProspectCallObserver;
use App\Jobs\PrepareAutomaticCall;
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
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
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
            'calls.enabled' => true,
            'calls.work_hours.start' => '08:00',
            'calls.work_hours.end' => '17:00',
            'calls.sms_frequency_hours' => 0,
        ]);
        Carbon::setTestNow('2026-11-13 10:00:00');
        CarbonImmutable::setTestNow('2026-11-13 10:00:00');

        // The model observers (SMS / email sending) are the CRM's, not under test
        Model::unsetEventDispatcher();

        (include base_path('database/migrations/2026_10_30_000001_create_workflow_tables.php'))->up();
        (include base_path('database/migrations/2026_10_30_000002_add_workflow_filter_memberships.php'))->up();
        (include base_path('database/migrations/2026_10_30_000003_add_waiting_event_to_workflow_executions.php'))->up();
        (include base_path('database/migrations/2026_10_30_000004_add_draft_graph_to_workflows.php'))->up();

        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('calls_enabled')->default(true);
            $table->softDeletes();
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
        $schema->create('groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name')->nullable();
            $table->softDeletes();
        });
        $schema->create('user_group', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('group_id');
        });
        $schema->create('prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile_phone_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->boolean('call_consent')->nullable();
            $table->boolean('do_not_call')->nullable();
            $table->string('call_timezone', 64)->nullable();
            $table->string('company_name')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        $schema->create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospect_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->softDeletes();
        });
        $schema->create('prospect_label', function (Blueprint $table) {
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('label_id');
        });
        $schema->create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name')->nullable();
            $table->text('body')->nullable();
            $table->string('subject')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->string('code', 40)->nullable();
            $table->boolean('marketing')->default(false);
            $table->boolean('disabled')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
        $schema->create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('for', 20)->default('prospect');
            $table->string('color', 7)->default('#333333');
            $table->string('bgcolor', 7)->default('#CCCCCC');
            $table->timestamps();
            $table->softDeletes();
        });
        $schema->create('labels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name');
            $table->string('color', 7)->default('#333333');
            $table->string('bgcolor', 7)->default('#CCCCCC');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
        $schema->create('fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('slug');
            $table->string('name', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('type', 20)->default('text');
            $table->string('for', 20)->default('prospect');
            $table->boolean('meta')->default(false);
            $table->unsignedInteger('order')->default(0);
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
            $table->string('subject')->nullable();
            $table->json('cc')->nullable();
            $table->json('email_layout')->nullable();
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

        DB::table('projects')->insert([
            ['id' => 1, 'name' => 'Heroes', 'calls_enabled' => true],
            ['id' => 2, 'name' => 'Other', 'calls_enabled' => true],
        ]);
        DB::table('users')->insert(['id' => 7, 'name' => 'Tonny', 'last_name' => 'Andritina', 'email' => 'tonny@example.com']);
        DB::table('prospects')->insert([
            ['id' => 1, 'project_id' => 1, 'first_name' => 'Marie', 'last_name' => 'Curie', 'email' => 'marie@example.com', 'mobile_phone_number' => '0611223344', 'company_name' => 'Radium', 'call_consent' => true],
            ['id' => 2, 'project_id' => 1, 'first_name' => 'Pierre', 'last_name' => 'Curie', 'email' => null, 'mobile_phone_number' => null, 'company_name' => null, 'call_consent' => true],
            ['id' => 3, 'project_id' => 1, 'first_name' => 'Irene', 'last_name' => 'Joliot', 'email' => 'irene@example.com', 'mobile_phone_number' => '0655667788', 'company_name' => null, 'call_consent' => true],
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
        CarbonImmutable::setTestNow();

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

    public function test_a_mail_server_is_named_after_its_service_not_its_address(): void
    {
        $registry = app(ProviderRegistry::class);
        $label = function (array $email) use ($registry) {
            DB::table('project_settings')->where('project_id', 1)->where('key', 'email')->update(['value' => json_encode($email)]);

            return $registry->forProject(1, 'email')[0];
        };

        $ovh = $label(['host' => 'smtp.ovh.net']);
        $this->assertSame('OVH (SMTP)', $ovh['label']);
        $this->assertSame('ovh', $ovh['operator']);

        // The relay of Brevo, under its former name too
        $this->assertSame('Brevo (SMTP)', $label(['host' => 'smtp-relay.sendinblue.com'])['label']);
        $brevo = $label(['host' => 'smtp-relay.brevo.com']);
        $this->assertSame('Brevo (SMTP)', $brevo['label']);
        $this->assertSame('brevo', $brevo['operator']);

        // The Brevo API key wins over a host left in the settings
        $this->assertSame('Brevo (email)', $label(['api_key' => 'K', 'host' => 'smtp.ovh.net'])['label']);

        // A server that is not known keeps its address
        $unknown = $label(['host' => 'mail.exemple-inconnu.fr']);
        $this->assertSame('SMTP mail.exemple-inconnu.fr', $unknown['label']);
        $this->assertSame('smtp', $unknown['operator']);
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

        $this->assertSame(WorkflowExecution::COMPLETED, $this->execution()->status, json_encode([
            'error' => $this->execution()->error,
            'node' => $this->nodeRow('a1')->error,
        ]));
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

    // Subject, copies and design of the email

    protected function copyFixtures(): void
    {
        DB::table('users')->insert([
            ['id' => 8, 'name' => 'Paul', 'last_name' => 'Martin', 'email' => 'paul@example.com'],
            ['id' => 9, 'name' => 'Lea', 'last_name' => 'Durand', 'email' => 'lea@example.com'],
            ['id' => 10, 'name' => 'Sans', 'last_name' => 'Email', 'email' => null],
            ['id' => 11, 'name' => 'Autre', 'last_name' => 'Projet', 'email' => 'autre@example.com'],
        ]);
        DB::table('user_project')->insert([
            ['user_id' => 7, 'project_id' => 1], ['user_id' => 8, 'project_id' => 1], ['user_id' => 9, 'project_id' => 1],
            ['user_id' => 10, 'project_id' => 1], ['user_id' => 11, 'project_id' => 2],
        ]);
        DB::table('groups')->insert([['id' => 1, 'project_id' => 1, 'name' => 'Commerciaux'], ['id' => 2, 'project_id' => 2, 'name' => 'Autre']]);
        // Paul is in the group and chosen on his own too; Lea only in the group; the one without email too
        DB::table('user_group')->insert([
            ['user_id' => 8, 'group_id' => 1], ['user_id' => 9, 'group_id' => 1], ['user_id' => 10, 'group_id' => 1], ['user_id' => 11, 'group_id' => 2],
        ]);
    }

    public function test_the_users_and_the_members_of_the_groups_are_put_in_copy_once_each(): void
    {
        $this->copyFixtures();
        $this->activate($this->graph($this->email(['cc_users' => [7, 8], 'cc_groups' => [1]])));

        $this->fire(1);

        $this->assertSame(WorkflowExecution::COMPLETED, $this->execution()->status);
        $this->assertSame(
            ['tonny@example.com', 'paul@example.com', 'lea@example.com'],
            array_column(Message::first()->cc, 'email') // Paul once, the user without address left out
        );
        $this->assertSame('Tonny Andritina', Message::first()->cc[0]['name']);
        $this->assertSame(3, $this->nodeRow('a1')->result['cc']);
    }

    public function test_the_prospect_is_never_put_in_copy_of_its_own_email(): void
    {
        $this->copyFixtures();
        DB::table('users')->where('id', 8)->update(['email' => 'MARIE@example.com']); // the prospect, in another case
        $this->activate($this->graph($this->email(['cc_users' => [8, 9]])));

        $this->fire(1);

        $this->assertSame(['lea@example.com'], array_column(Message::first()->cc, 'email'));
    }

    public function test_without_copies_the_message_has_none(): void
    {
        $this->activate($this->graph($this->email()));

        $this->fire(1);

        $this->assertNull(Message::first()->cc);
        $this->assertNull(Message::first()->subject);
    }

    public function test_the_subject_takes_the_variables_of_the_prospect(): void
    {
        $this->activate($this->graph($this->email(['subject' => "Bienvenue {{prospect.firstname}}\r\nBcc: x@evil.com"])));

        $this->fire(1);

        $this->assertSame('Bienvenue Marie Bcc: x@evil.com', Message::first()->subject); // one line: no header injection
    }

    public function test_the_html_of_the_email_is_cleaned_but_keeps_its_design(): void
    {
        $html = '<h2 style="color:#c0341d">Bonjour</h2><p onclick="x()">Texte <b>gras</b> <a href="javascript:alert(1)">lien</a> <a href="https://heroes.test">ok</a></p><script>alert(1)</script><iframe src="https://evil.test"></iframe><img src="https://heroes.test/a.png" onerror="x()">';
        $this->activate($this->graph($this->email(['body' => $html])));

        $this->fire(1);

        $body = Message::first()->body;
        $this->assertStringContainsString('<h2 style="color:#c0341d">Bonjour</h2>', $body);
        $this->assertStringContainsString('<b>gras</b>', $body);
        $this->assertStringContainsString('<a href="https://heroes.test">ok</a>', $body);
        $this->assertStringContainsString('<img src="https://heroes.test/a.png">', $body);
        foreach (['onclick', 'onerror', 'javascript:', '<script', '<iframe'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $body);
        }
    }

    public function test_the_people_in_copy_must_belong_to_the_project(): void
    {
        $this->copyFixtures();

        $errors = $this->errors($this->graph($this->email(['cc_users' => [7, 11], 'cc_groups' => [2]])));

        $this->assertContains('Un utilisateur mis en copie de l\'action "a1" ne fait plus partie de ce projet.', $errors);
        $this->assertContains('Un groupe mis en copie de l\'action "a1" n\'existe plus dans ce projet.', $errors);
        $this->assertSame([], $this->errors($this->graph($this->email(['cc_users' => [7, 8], 'cc_groups' => [1]]))));
        $this->assertNotSame([], $this->errors($this->graph($this->email(['cc_users' => 'all']))));
    }

    public function test_an_email_without_visible_content_is_refused(): void
    {
        $this->assertContains('L\'action "a1" (Envoyer un email) n\'a pas de contenu.', $this->errors($this->graph($this->email(['body' => '<p><br></p>']))));
    }

    // Design of the email

    public function test_the_design_is_complete_and_only_keeps_what_it_knows(): void
    {
        $design = EmailDesign::normalize([
            'style' => 'simple', 'font' => 'comic-sans', 'header_color' => '#ABCDEF', 'accent_color' => 'red; background:url(x)',
            'logo' => 'https://evil.test/x.png', 'show_footer' => false, 'brand_name' => '<b>Mon</b> projet', 'closing' => '',
            'unknown' => 'x',
        ], 'Heroes');

        $this->assertSame('simple', $design['style']);
        $this->assertSame('arial', $design['font']);             // unknown font: the default
        $this->assertSame('#abcdef', $design['header_color']);   // valid colour, normalised
        $this->assertSame('#4a8796', $design['accent_color']);   // not a colour: the default
        $this->assertSame('none', $design['logo']);              // only the logo of the project
        $this->assertFalse($design['show_footer']);
        $this->assertSame('Mon projet', $design['brand_name']);  // plain text
        $this->assertSame('', $design['closing']);               // emptied on purpose
        $this->assertSame('Heroes', $design['signature_name']);  // not given: the default
        $this->assertArrayNotHasKey('unknown', $design);
    }

    public function test_the_choices_of_the_editor_are_the_ones_of_the_server(): void
    {
        // Same lists as resources/js/utils/workflow-email.js (its test holds the other half)
        $this->assertSame(
            ['accent_color', 'background_color', 'brand_label', 'brand_name', 'category', 'closing', 'font', 'header_color', 'logo', 'rights',
                'sent_by', 'show_footer', 'show_signature', 'show_title', 'signature_name', 'style', 'text_color'],
            collect(array_keys(EmailDesign::defaults('Heroes')))->sort()->values()->all()
        );
        $this->assertSame(['arial', 'verdana', 'tahoma', 'trebuchet', 'georgia', 'times'], array_keys(EmailDesign::FONTS));
        $this->assertSame(['branded', 'simple'], EmailDesign::STYLES);
        $this->assertSame('#163b52', EmailDesign::defaults()['header_color']);
        $this->assertSame('', EmailDesign::defaults('Heroes')['brand_label']);
    }

    public function test_a_design_with_wrong_values_is_refused(): void
    {
        $errors = fn (array $design) => $this->errors($this->graph($this->email(['design' => $design])));

        $this->assertSame([], $errors(['style' => 'branded', 'header_color' => '#112233', 'font' => 'georgia']));
        $this->assertNotSame([], $errors(['style' => 'neon']));
        $this->assertNotSame([], $errors(['header_color' => 'red']));
        $this->assertNotSame([], $errors(['font' => 'comic-sans']));
        $this->assertNotSame([], $errors(['brand_name' => str_repeat('a', 300)]));
        $this->assertNotSame([], $this->errors($this->graph($this->email(['design' => 'branded']))));
    }

    public function test_the_email_is_drawn_with_the_design_and_the_texts_are_escaped(): void
    {
        $html = EmailDesign::render(
            ['header_color' => '#112233', 'brand_name' => '<script>x</script>Mon projet', 'brand_label' => 'NEWSLETTER', 'show_title' => true],
            'Bienvenue <Marie>',
            '<p style="color:#c0341d">Bonjour <b>Marie</b></p>',
            'Heroes'
        );

        $this->assertStringContainsString('background:#112233', $html);
        $this->assertStringContainsString('NEWSLETTER', $html);
        $this->assertStringContainsString('<p style="color:#c0341d">Bonjour <b>Marie</b></p>', $html); // the body is HTML
        $this->assertStringContainsString('Bienvenue &lt;Marie&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_the_parts_of_the_design_can_be_hidden_and_the_simple_style_has_no_header(): void
    {
        $branded = EmailDesign::render(['show_footer' => false, 'show_signature' => false, 'show_title' => false], 'Objet', '<p>Corps</p>', 'Heroes');
        $this->assertStringNotContainsString('Tous droits réservés', $branded);
        $this->assertStringNotContainsString('Bien cordialement', $branded);
        $this->assertStringNotContainsString('<h1', $branded); // the subject is not repeated as a title
        $this->assertStringContainsString('border-radius:12px 12px 0 0', $branded); // header band

        $simple = EmailDesign::render(['style' => 'simple'], 'Objet', '<p>Corps</p>', 'Heroes');
        $this->assertStringNotContainsString('border-radius:12px 12px 0 0', $simple);
        $this->assertStringContainsString('Bien cordialement', $simple);
    }

    public function test_the_design_chosen_is_kept_with_the_message_and_none_means_the_usual_look(): void
    {
        $this->activate($this->graph($this->email(['design' => ['style' => 'simple', 'header_color' => '#112233', 'bogus' => 1]])));
        $this->fire(1);

        $layout = Message::first()->email_layout;
        $this->assertSame('simple', $layout['style']);
        $this->assertSame('#112233', $layout['header_color']);
        $this->assertSame('Heroes', $layout['brand_name']); // the name of the project fills what was not chosen
        $this->assertArrayNotHasKey('bogus', $layout);
    }

    public function test_without_a_design_the_message_has_none(): void
    {
        $this->activate($this->graph($this->email()));
        $this->fire(1);

        $this->assertNull(Message::first()->email_layout);
    }

    // Mailing plan: variables, rules, templates, import

    protected function resolver(int $id = 1): VariableResolver
    {
        return new VariableResolver(Prospect::findOrFail($id));
    }

    protected function setMeta(int $id, array $meta): void
    {
        DB::table('prospects')->where('id', $id)->update(['meta' => json_encode($meta)]);
    }

    protected function setRules(array $rules = [], int $projectId = 1): void
    {
        DB::table('project_settings')->insert(['project_id' => $projectId, 'key' => 'mailing_rules', 'value' => json_encode($rules)]);
    }

    protected function template(array $values = []): int
    {
        return DB::table('message_templates')->insertGetId($values + [
            'project_id' => 1, 'name' => 'CL NC1', 'code' => 'NC1', 'subject' => 'Bonjour {{prospect.firstname}}', 'marketing' => 1, 'disabled' => 0,
            'body' => '<!DOCTYPE html><html><head><title>Titre</title></head><body><p>Votre dossier {{field.centre}}</p></body></html>',
        ]);
    }

    public function test_a_custom_field_and_the_computed_variables_are_read(): void
    {
        $this->setMeta(1, ['centre' => 'Lyon <Part-Dieu>', 'liste' => ['x']]);
        DB::table('prospects')->where('id', 1)->update(['date_of_birth' => now()->subYears(57)->toDateString()]);

        $resolver = $this->resolver();

        $this->assertSame('Lyon &lt;Part-Dieu&gt;', $resolver->render('{{field.centre}}', 'html')); // escaped like the others
        $this->assertSame('', $resolver->render('{{field.liste}}'));  // only text and numbers
        $this->assertSame('', $resolver->render('{{field.absent}}'));
        $this->assertSame('57 oui', $resolver->render('{{prospect.age}} {{prospect.plus_50}}'));
        $this->assertStringContainsString('signature=', $resolver->render('{{prospect.unsubscribe_url}}'));
        $this->assertStringContainsString('/mailing/unsubscribe/1', $resolver->render('{{prospect.unsubscribe_url}}'));

        // Without a birth date, nobody is "plus 50"
        DB::table('prospects')->where('id', 1)->update(['date_of_birth' => null]);
        $this->assertSame(' non', $this->resolver()->render('{{prospect.age}} {{prospect.plus_50}}'));
    }

    public function test_the_conditions_keep_the_right_part_of_the_text(): void
    {
        $this->setMeta(1, ['pays' => 'BE', 'mensualite' => '', 'centre' => 'Lyon']);
        $r = $this->resolver();

        $this->assertSame('belge', $r->render('{% if field.pays == "BE" %}belge{% else %}français{% endif %}'));
        $this->assertSame('français', $r->render('{% if field.pays == "FR" %}belge{% else %}français{% endif %}'));
        $this->assertSame('', $r->render('{% if field.mensualite != "" %}x{% endif %}'));
        $this->assertSame('x', $r->render('{% if field.mensualite == "" %}x{% endif %}'));
        $this->assertSame('x', $r->render('{% if field.centre %}x{% endif %}'));      // not empty
        $this->assertSame('', $r->render('{% if field.absent %}x{% endif %}'));
        $this->assertSame('A-C', $r->render('A-{% if field.centre == "Paris" %}B{% else %}C{% endif %}'));
        // Nested, and a condition false hides the conditions inside it
        $this->assertSame('1', $r->render('{% if field.centre == "Lyon" %}{% if field.pays == "BE" %}1{% else %}2{% endif %}{% else %}3{% endif %}'));
        $this->assertSame('3', $r->render('{% if field.centre == "Paris" %}{% if field.pays == "BE" %}1{% else %}2{% endif %}{% else %}3{% endif %}'));
        // The values are escaped in the text kept
        $this->assertSame('<p>Lyon</p>', $r->render('{% if field.centre %}<p>{{field.centre}}</p>{% endif %}', 'html'));
    }

    public function test_the_problems_of_the_conditions_and_the_variables_are_named(): void
    {
        $this->assertSame([], VariableResolver::conditionProblems('{% if field.a == "x" %}1{% else %}2{% endif %}'));
        $this->assertNotSame([], VariableResolver::conditionProblems('{% if field.a %}1'));
        $this->assertNotSame([], VariableResolver::conditionProblems('1{% endif %}'));
        $this->assertNotSame([], VariableResolver::conditionProblems('{% if field.a %}1{% else %}2{% else %}3{% endif %}'));
        $this->assertNotSame([], VariableResolver::conditionProblems('{% for x in y %}{% endfor %}')); // not understood
        $this->assertSame(['prospect.nom'], VariableResolver::unknown('{% if prospect.nom == "x" %}{{field.libre}}{% endif %}'));
    }

    public function test_the_rules_say_who_is_never_written_to(): void
    {
        $rules = app(MailingRules::class);

        // A project without rules: nothing changes
        $this->assertNull($rules->never(Prospect::find(1)));

        $this->setRules(['optout_labels' => [9]]);
        $rules = new MailingRules();
        $this->assertNull($rules->never(Prospect::find(1)));

        $this->setMeta(1, ['mailing_optout' => '2026-11-01']);
        $this->assertSame('optout', $rules->never(Prospect::find(1)));

        $this->setMeta(1, ['suppression_demandee' => 'oui']);
        $this->assertSame('deletion', $rules->never(Prospect::find(1)));

        $this->setMeta(1, ['mailing_optout' => 'non']); // "non" is not a refusal
        $this->assertNull($rules->never(Prospect::find(1)));

        DB::table('prospects')->where('id', 1)->update(['date_of_birth' => now()->subYears(17)->toDateString()]);
        $this->assertSame('minor', $rules->never(Prospect::find(1)));
        DB::table('prospects')->where('id', 1)->update(['date_of_birth' => now()->subYears(18)->toDateString()]);
        $this->assertNull($rules->never(Prospect::find(1)));

        DB::table('prospect_label')->insert(['prospect_id' => 1, 'label_id' => 9]);
        $this->assertSame('do_not_contact', $rules->never(Prospect::find(1)));
    }

    public function test_the_days_around_an_appointment_are_quiet_and_one_email_a_day_is_the_limit(): void
    {
        $this->setRules();
        $rules = new MailingRules();
        $prospect = Prospect::find(1);

        $this->assertFalse($rules->quiet($prospect));
        DB::table('events')->insert(['prospect_id' => 1, 'started_at' => now()->addDays(10)]);
        $this->assertFalse($rules->quiet($prospect));
        DB::table('events')->insert(['prospect_id' => 1, 'started_at' => now()->addDays(3)]);
        $this->assertTrue($rules->quiet($prospect));
        DB::table('events')->delete();
        DB::table('events')->insert(['prospect_id' => 1, 'started_at' => now()->subHours(20)]); // the day after
        $this->assertTrue($rules->quiet($prospect));
        DB::table('events')->delete();
        DB::table('events')->insert(['prospect_id' => 1, 'started_at' => now()->subDays(3)]);
        $this->assertFalse($rules->quiet($prospect));

        // One email a day: none yet, then one is enough to wait for tomorrow
        $this->assertNull($rules->postponedUntil($prospect));
        DB::table('messages')->insert(['prospect_id' => 1, 'thread_id' => 1, 'from_user' => 1, 'subject' => 'X', 'created_at' => now(), 'updated_at' => now()]);
        $until = $rules->postponedUntil($prospect);
        $this->assertNotNull($until);
        $this->assertTrue($until->isFuture());

        // A reply waiting for a person stops the marketing too
        DB::table('messages')->delete();
        $this->assertNull($rules->postponedUntil($prospect));
        $this->setMeta(1, ['tache_ouverte' => 'oui']);
        $this->assertNotNull($rules->postponedUntil(Prospect::find(1)));
    }

    public function test_a_template_is_sent_as_a_whole_email_with_its_own_subject(): void
    {
        $this->template();
        $this->setMeta(1, ['centre' => 'Lyon']);
        $this->activate($this->graph(['action' => 'send_email', 'provider' => 'email:project', 'thread' => 1, 'template' => 'NC1']));

        $this->fire(1);

        $message = Message::first();
        $this->assertSame(WorkflowExecution::COMPLETED, $this->execution()->status);
        $this->assertSame('Bonjour Marie', $message->subject);
        $this->assertSame('<p>Votre dossier Lyon</p>', $message->body); // the body of the page, no <html>
        $this->assertSame('raw', $message->email_layout['style']);
    }

    public function test_a_template_switched_off_is_never_sent(): void
    {
        $this->template(['disabled' => 1]);
        $this->activate($this->graph(['action' => 'send_email', 'provider' => 'email:project', 'thread' => 1, 'template' => 'NC1']));

        $this->fire(1);

        $this->assertSame(0, Message::count());
        $this->assertSame(WorkflowExecution::COMPLETED, $this->execution()->status);
        $this->assertSame('disabled', $this->nodeRow('a1')->result['skipped']);
    }

    public function test_nobody_is_written_to_against_their_wish_even_by_a_service_email(): void
    {
        $this->setRules();
        $this->template(['marketing' => 0]);
        $this->setMeta(1, ['mailing_optout' => 'oui']);
        $this->activate($this->graph(['action' => 'send_email', 'provider' => 'email:project', 'thread' => 1, 'template' => 'NC1']));

        $this->fire(1);

        $this->assertSame(0, Message::count());
        $this->assertSame('optout', $this->nodeRow('a1')->result['skipped']);
    }

    public function test_a_marketing_email_keeps_quiet_around_an_appointment_but_a_service_email_goes(): void
    {
        $this->setRules();
        DB::table('events')->insert(['prospect_id' => 1, 'started_at' => now()->addDays(2)]);
        $this->template(['code' => 'MK', 'marketing' => 1]);
        $this->template(['code' => 'SV', 'marketing' => 0]);
        $this->activate($this->graph(
            ['action' => 'send_email', 'provider' => 'email:project', 'thread' => 1, 'template' => 'MK'],
            ['action' => 'send_email', 'provider' => 'email:project', 'thread' => 1, 'template' => 'SV']
        ));

        $this->fire(1);

        $this->assertSame('quiet_window', $this->nodeRow('a1')->result['skipped']);
        $this->assertArrayNotHasKey('skipped', $this->nodeRow('a2')->result);
        $this->assertSame(1, Message::count());
    }

    public function test_a_second_marketing_email_of_the_day_waits_for_tomorrow(): void
    {
        $this->setRules();
        $this->template();
        DB::table('messages')->insert(['prospect_id' => 1, 'thread_id' => 1, 'from_user' => 1, 'subject' => 'Déjà un', 'created_at' => now(), 'updated_at' => now()]);
        $this->activate($this->graph(['action' => 'send_email', 'provider' => 'email:project', 'thread' => 1, 'template' => 'NC1']));

        $this->fire(1);

        $execution = $this->execution();
        $this->assertSame(WorkflowExecution::WAITING, $execution->status); // not sent, not lost
        $this->assertTrue($execution->resume_at->isFuture());
        $this->assertSame(1, Message::count());
    }

    public function test_a_marketing_email_told_to_skip_is_dropped_not_postponed(): void
    {
        $this->setRules();
        $this->template();
        DB::table('messages')->insert(['prospect_id' => 1, 'thread_id' => 1, 'from_user' => 1, 'subject' => 'Déjà un', 'created_at' => now(), 'updated_at' => now()]);
        $this->activate($this->graph(['action' => 'send_email', 'provider' => 'email:project', 'thread' => 1, 'template' => 'NC1', 'if_busy' => 'skip']));

        $this->fire(1);

        $this->assertSame(WorkflowExecution::COMPLETED, $this->execution()->status); // done: not waiting for tomorrow
        $this->assertSame('busy', $this->nodeRow('a1')->result['skipped']);
        $this->assertSame(1, Message::count());
        $this->assertNotSame([], $this->errors($this->graph(['action' => 'send_email', 'provider' => 'email:project', 'thread' => 1, 'template' => 'NC1', 'if_busy' => 'never'])));
    }

    public function test_a_template_that_does_not_exist_is_refused_when_publishing(): void
    {
        $this->assertContains(
            'Le modèle d\'email de l\'action "a1" n\'existe plus dans ce projet.',
            $this->errors($this->graph(['action' => 'send_email', 'provider' => 'email:project', 'thread' => 1, 'template' => 'NOPE']))
        );
        $this->template();
        $this->assertSame([], $this->errors($this->graph(['action' => 'send_email', 'provider' => 'email:project', 'thread' => 1, 'template' => 'NC1'])));
    }

    public function test_the_body_of_a_whole_page_is_taken_with_its_title(): void
    {
        $parts = EmailHtml::parts('<!DOCTYPE html><html><head><title>Mon &amp; objet</title></head><body style="x"><p>Corps</p></body></html>');

        $this->assertSame('Mon & objet', $parts['title']);
        $this->assertSame('<p>Corps</p>', $parts['body']);
        $this->assertSame(['title' => null, 'body' => '<p>Seul</p>'], EmailHtml::parts('<p>Seul</p>'));
    }

    public function test_the_syntax_of_brevo_becomes_the_one_of_the_crm(): void
    {
        $config = ['URL_RAPPEL' => 'https://ex.fr/rappel', 'EMAIL_DPO' => 'dpo@ex.fr'];
        $text = '{{ contact.PRENOM }} {{ contact.DATE_RDV }} {{ contact.PLUS_50 }} {% if contact.PAYS == "BE" %}be{% endif %} {{ unsubscribe }} {URL_RAPPEL}?e={{ contact.EMAIL }} {EMAIL_DPO} {INCONNU}';

        $converted = KitImporter::convert($text, $config);

        $this->assertSame(
            '{{prospect.firstname}} {{field.date_rdv}} {{prospect.plus_50}} {% if field.pays == "BE" %}be{% endif %} {{prospect.unsubscribe_url}} https://ex.fr/rappel?e={{prospect.email}} dpo@ex.fr {INCONNU}',
            $converted
        );
        $this->assertSame(['réglage non renseigné dans config.json : {INCONNU}'], KitImporter::problems($converted));
        $this->assertSame([], KitImporter::problems(str_replace('{INCONNU}', '', $converted)));
    }

    protected function kit(array $messages, array $config = []): string
    {
        $dir = sys_get_temp_dir() . '/kit-' . uniqid();
        mkdir("$dir/templates", 0777, true);
        file_put_contents("$dir/config.json", json_encode($config + ['FOURCHETTE_PRIX' => '1 100 €', 'URL_RAPPEL' => 'https://ex.fr/rappel']));
        file_put_contents("$dir/catalogue.json", json_encode([
            'attributs' => ['PRENOM' => 'text', 'CENTRE' => 'text', 'PLUS_50' => 'text', 'DATE_RDV' => 'text'],
            'attributs_description' => ['CENTRE' => 'Fiche centre'],
            'messages' => $messages,
        ]));

        foreach ($messages as $message) {
            if (!empty($message['fichier_html'])) {
                file_put_contents("$dir/{$message['fichier_html']}", $message['_html'] ?? '<p>Bonjour {{ contact.PRENOM }}, {{ contact.CENTRE }}</p>');
            }
        }

        return $dir;
    }

    public function test_the_kit_is_imported_once_and_can_be_imported_again(): void
    {
        $dir = $this->kit([
            ['code' => 'NC1', 'nom_modele' => 'CL NC1 · Suite', 'objet' => 'Bonjour {{ contact.PRENOM }}', 'marketing' => true, 'statut' => 'actif', 'fichier_html' => 'templates/NC1.html'],
            ['code' => 'C07', 'nom_modele' => 'CL C07', 'objet' => 'En attente', 'marketing' => false, 'statut' => 'desactive', 'fichier_html' => 'templates/C07.html'],
            ['code' => 'PC-V', 'canal' => 'Vocal WhatsApp', 'texte' => 'Bonjour'],
            ['code' => 'BAD', 'objet' => 'x', 'statut' => 'actif', 'fichier_html' => 'templates/BAD.html', '_html' => '<p>{{ contact.CENTRE }} {% if contact.X == "a" %}jamais fermé</p>'],
        ]);

        $report = app(KitImporter::class)->import(1, $dir);

        // Fields: what the plan names (the ones the CRM knows are not) and the ones of the rules
        $this->assertEqualsCanonicalizing(['centre', 'date_rdv', 'mailing_optout', 'suppression_demandee', 'avis_enquete'], $report['fields']);
        $this->assertSame(2, $report['templates']['created']);
        $this->assertSame('pas un email (Vocal WhatsApp)', $report['skipped']['PC-V']);
        $this->assertArrayHasKey('BAD', $report['problems']); // a template the CRM could not run is not imported
        $this->assertTrue($report['settings']);

        $nc1 = DB::table('message_templates')->where('code', 'NC1')->first();
        $this->assertSame('Bonjour {{prospect.firstname}}', $nc1->subject);
        $this->assertSame('<p>Bonjour {{prospect.firstname}}, {{field.centre}}</p>', $nc1->body);
        $this->assertSame(1, (int) $nc1->marketing);
        $this->assertSame(1, (int) DB::table('message_templates')->where('code', 'C07')->value('disabled')); // never sent
        $this->assertSame('meta', DB::table('fields')->where('slug', 'centre')->exists() ? 'meta' : 'none');
        $this->assertSame(1, (int) json_decode(DB::table('project_settings')->where('key', 'mailing_rules')->value('value'), true)['max_per_day']);

        // Again: the same, updated, nothing doubled
        DB::table('message_templates')->where('code', 'NC1')->update(['subject' => 'modifié']);
        $again = app(KitImporter::class)->import(1, $dir);

        $this->assertSame([], $again['fields']);
        $this->assertSame(['created' => 0, 'updated' => 2], $again['templates']);
        $this->assertFalse($again['settings']);
        $this->assertSame(2, DB::table('message_templates')->count());
        $this->assertSame('Bonjour {{prospect.firstname}}', DB::table('message_templates')->where('code', 'NC1')->value('subject'));
    }

    public function test_the_kit_is_not_imported_while_the_price_is_not_validated_and_a_dry_run_writes_nothing(): void
    {
        $dir = $this->kit([['code' => 'NC1', 'objet' => 'x', 'statut' => 'actif', 'fichier_html' => 'templates/NC1.html']], ['FOURCHETTE_PRIX' => 'À VALIDER PAR LA DIRECTION']);

        $report = app(KitImporter::class)->import(1, $dir);
        $this->assertNotSame([], $report['problems']);
        $this->assertSame(0, DB::table('message_templates')->count());

        $dry = app(KitImporter::class)->import(1, $dir, ['allow_placeholder' => true, 'dry_run' => true]);
        $this->assertSame(1, $dry['templates']['created']);
        $this->assertSame(0, DB::table('message_templates')->count());
        $this->assertSame(0, DB::table('fields')->count());
    }

    public function test_the_link_to_stop_the_emails_is_signed_and_marks_the_prospect(): void
    {
        $url = URL::signedRoute('mailing.unsubscribe', ['prospect' => 1]);

        $this->get($url)->assertOk()->assertSee('Vous ne recevrez plus');
        $this->assertSame(now()->toDateString(), json_decode(DB::table('prospects')->where('id', 1)->value('meta'), true)['mailing_optout']);

        // What the rules read
        $this->setRules();
        $this->assertSame('optout', (new MailingRules())->never(Prospect::find(1)));

        // Not a link made by the CRM: refused, and nobody else is marked
        $this->get('/mailing/unsubscribe/3')->assertForbidden();
        $this->get(preg_replace('/signature=[^&]+/', 'signature=faux', URL::signedRoute('mailing.unsubscribe', ['prospect' => 3])))->assertForbidden();
        $this->assertNull(DB::table('prospects')->where('id', 3)->value('meta'));
    }

    // Mailing plan: the sequences as automations

    protected function miniCatalogue(): array
    {
        $step = fn (int $days, string $code, ?string $variant = null) => ['attendre_jours' => $days, 'envoyer' => $code, 'variante' => $variant];

        return [
            'listes' => ['CL · Premier contact', 'CL · Nurturing chaud', 'CL · Nurturing froid'],
            'messages' => array_map(fn ($code) => ['code' => $code, 'nom_modele' => "CL $code · Objet de $code"], ['PC1', 'PC2', 'PC3', 'N12', 'N12-50', 'NF2', 'H03', 'H06']),
            'scenarios' => [
                ['nom' => 'CL · Premier contact', 'liste_entree' => 'CL · Premier contact', 'etapes' => [
                    $step(0, 'PC1'), $step(1, 'PC2', 'Condition avant envoi : JOINT différent de « oui »'), $step(2, 'PC3', 'Condition avant envoi : JOINT différent de « oui »'),
                ]],
                ['nom' => 'CL · Nurturing chaud', 'liste_entree' => 'CL · Nurturing chaud', 'etapes' => [
                    $step(0, 'PC1'), $step(3, 'N12', 'Si PLUS_50 = « oui » : envoyer N12-50 à la place'),
                ]],
                ['nom' => 'CL · Nurturing froid', 'liste_entree' => 'CL · Nurturing froid', 'etapes' => [
                    $step(0, 'PC1'), $step(7, 'NF2', 'Si HC_MOTIF = « pas_eligible » : H03 ; si motif médical : H06'),
                ]],
            ],
        ];
    }

    protected function miniKit(): string
    {
        $dir = sys_get_temp_dir() . '/plan-' . uniqid();
        mkdir($dir, 0777, true);
        file_put_contents("$dir/catalogue.json", json_encode($this->miniCatalogue()));

        foreach (['PC1', 'PC2', 'PC3', 'N12', 'N12-50', 'NF2', 'H03', 'H06'] as $code) {
            $this->template(['code' => $code, 'name' => "CL $code"]);
        }

        return $dir;
    }

    public function test_the_variants_of_a_step_are_read_from_what_the_plan_says(): void
    {
        $this->assertSame([], PlanGenerator::alternatives(''));
        $this->assertSame([], PlanGenerator::alternatives('Condition avant envoi : JOINT différent de « oui »'));

        $this->assertSame(
            [['field' => 'plus_50', 'is' => 'oui', 'code' => 'N12-50', 'label' => 'Le contact a 50 ans ou plus ?']],
            PlanGenerator::alternatives('Si PLUS_50 = « oui » : envoyer N12-50 à la place')
        );

        $motif = PlanGenerator::alternatives('Si HC_MOTIF = « pas_eligible » : H03 ; si motif médical : H06');
        $this->assertSame(['H03', 'H06'], array_column($motif, 'code'));
        $this->assertSame(['pas_eligible', 'medical'], array_column($motif, 'is'));
    }

    public function test_a_sequence_is_drawn_as_a_trigger_then_waits_and_emails(): void
    {
        $catalogue = $this->miniCatalogue();
        $specs = PlanGenerator::specs($catalogue);
        $first = (new PlanBuilder())->build($specs[0], ['CL · Premier contact' => 11, 'CL · Nurturing chaud' => 12], collect($catalogue['messages'])->keyBy('code')->all());

        $byId = collect($first['nodes'])->keyBy('id');
        $types = array_map(fn ($n) => $n['type'] . ($n['config']['template'] ?? ''), $first['nodes']);

        // trigger, PC1, wait 1, PC2, wait 2, PC3, wait 1, label "chaud", end
        $this->assertSame(['trigger', 'actionPC1', 'wait', 'actionPC2', 'wait', 'actionPC3', 'wait', 'action', 'end'], $types);
        $this->assertSame([1, 2, 1], array_values(array_map(fn ($n) => $n['config']['days'], array_filter($first['nodes'], fn ($n) => $n['type'] === 'wait'))));

        // The sequence starts with the label, and stops when the prospect is joined or has an appointment
        $trigger = $byId['trigger']['config'];
        $this->assertSame('prospect.filter_entered', $trigger['trigger']);
        $this->assertSame([11], $trigger['items'][0]['filters']['withLabels']);
        $this->assertSame([['field' => 'rdv_fixe', 'is_not' => 'oui'], ['field' => 'joint', 'is_not' => 'oui']], array_slice($trigger['items'], 1));
        $this->assertTrue($trigger['exit_on_leave']);

        // The end of the sequence: the contact goes on to the next one
        $label = collect($first['nodes'])->firstWhere('config.action', 'prospect-attach-label');
        $this->assertSame(12, $label['config']['value']['label']);

        // Every node but the last leads somewhere, and the names say which email it is
        $this->assertCount(8, $first['edges']);
        $this->assertSame('PC2 · Objet de PC2', $first['nodes'][3]['name']);
    }

    public function test_a_step_with_variants_asks_before_choosing_the_email(): void
    {
        $catalogue = $this->miniCatalogue();
        $specs = PlanGenerator::specs($catalogue);
        $graph = (new PlanBuilder())->build($specs[1], ['CL · Nurturing chaud' => 12, 'CL · Nurturing froid' => 13], collect($catalogue['messages'])->keyBy('code')->all());

        $condition = collect($graph['nodes'])->firstWhere('type', 'condition');
        $this->assertSame([['field' => 'plus_50', 'is' => 'oui']], $condition['config']['items']);

        $edges = collect($graph['edges'])->where('from', $condition['id']);
        $yes = $graph['nodes'][array_search($edges->firstWhere('branch', 'yes')['to'], array_column($graph['nodes'], 'id'))];
        $no = $graph['nodes'][array_search($edges->firstWhere('branch', 'no')['to'], array_column($graph['nodes'], 'id'))];
        $this->assertSame('N12-50', $yes['config']['template']);
        $this->assertSame('N12', $no['config']['template']);

        // The two emails lead to the same next step
        $next = collect($graph['edges'])->whereIn('from', [$yes['id'], $no['id']])->pluck('to')->unique();
        $this->assertCount(1, $next);

        // With two variants: one question after the other, the usual email last
        $graph = (new PlanBuilder())->build($specs[2], ['CL · Nurturing froid' => 13], collect($catalogue['messages'])->keyBy('code')->all());
        $this->assertCount(2, array_filter($graph['nodes'], fn ($n) => $n['type'] === 'condition'));
        $this->assertEqualsCanonicalizing(['PC1', 'H03', 'H06', 'NF2'], array_filter(array_column(array_column($graph['nodes'], 'config'), 'template')));
    }

    public function test_the_plan_becomes_drafts_that_pass_the_validation_and_are_redrawn_safely(): void
    {
        $dir = $this->miniKit();
        $generator = app(PlanGenerator::class);

        $report = $generator->generate(1, $dir);

        $this->assertCount(3, $report['labels']['created']);
        $this->assertSame(3, DB::table('labels')->count());
        $this->assertSame(1, DB::table('categories')->count());
        $this->assertSame(['créée', 'créée', 'créée'], array_column($report['workflows'], 'status'));
        $this->assertSame([[], [], []], array_column($report['workflows'], 'problems')); // emails, operator and tests all known

        $workflow = Workflow::where('name', 'CL · Premier contact')->first();
        $this->assertSame('draft', $workflow->status);
        $this->assertNull($workflow->active_version_id);   // never started by itself: a person checks, then activates
        $this->assertSame(0, WorkflowExecution::count());

        // Again: redrawn, nothing doubled, labels reused
        $again = $generator->generate(1, $dir);
        $this->assertSame(['redessinée', 'redessinée', 'redessinée'], array_column($again['workflows'], 'status'));
        $this->assertSame([], $again['labels']['created']);
        $this->assertSame(3, Workflow::count());
        $this->assertSame(3, DB::table('labels')->count());

        // Once published, what a person did to it is not touched
        app(WorkflowService::class)->publish($workflow, $workflow->draft_graph);
        $workflow->update(['draft_graph' => ['nodes' => [], 'edges' => []]]);
        $third = $generator->generate(1, $dir);
        $this->assertStringStartsWith('laissée telle quelle', $third['workflows']['CL · Premier contact']['status']);
        $this->assertSame([], $workflow->fresh()->draft_graph['nodes']);
    }

    protected function eventCatalogue(): array
    {
        $mail = fn (string $code, string $when, string $trigger = 'api_crm', string $status = 'actif') => [
            'code' => $code, 'nom_modele' => "CL $code · Objet", 'quand' => $when, 'declenchement' => $trigger, 'statut' => $status, 'fichier_html' => "templates/$code.html", 'marketing' => true,
        ];

        return [
            'listes' => ['CL · Tous les contacts (newsletter)'],
            'scenarios' => [],
            'messages' => [
                $mail('AB1', 'Immédiat, dès que le bilan est fixé'),
                $mail('AB2', 'Le lendemain de la prise de rendez-vous'),
                $mail('AB3', '7 jours avant le bilan'),
                $mail('AB4', '5 jours avant le bilan'),
                $mail('OP3', 'Le lendemain de l\'intervention'),
                $mail('G01', 'J+45 · si avis de 4 ou 5'),
                $mail('P06', '1 an après l\'intervention'),
                $mail('G05', '1 an après, avec P06'),
                $mail('PB7', 'Le lendemain du bilan'),
                $mail('D07', 'J+1 · devis refusé'),
                $mail('RM1', '30 minutes après un rendez-vous manqué'),
                $mail('N35', 'Frein budget'),
                $mail('T05', 'Immédiat · suppression des données'),
                $mail('X99', 'Désactivé', 'desactive', 'desactive'),
                $mail('L18', 'Janvier, 1er numéro', 'campagne_newsletter'),
                $mail('N05', 'Février, 2e numéro', 'campagne_newsletter'),
                $mail('N05-50', 'Février, 2e numéro · remplace N05 pour les + 50 ans', 'campagne_newsletter'),
                $mail('N01', 'Juillet, 1er numéro', 'campagne_newsletter'),
                $mail('N01-S', 'Juillet, 1er numéro · remplace N01 pour les contacts sans correction', 'campagne_newsletter'),
                $mail('L12', '6 mois après la demande (remplace le numéro de la quinzaine)', 'campagne_newsletter'),
                $mail('N41', 'Numéro de réserve', 'campagne_newsletter'),
            ],
        ];
    }

    protected function flowOf(array $flows, string $name): array
    {
        return collect($flows)->firstWhere('name', $name) ?? $this->fail("No flow named $name: " . implode(' | ', array_column($flows, 'name')));
    }

    public function test_every_active_email_of_the_plan_is_planned_once_and_the_disabled_ones_never(): void
    {
        $flows = EventPlan::flows($this->eventCatalogue());
        $codes = [];

        foreach ($flows as $flow) {
            foreach ($flow['steps'] as $step) {
                isset($step['send']) && $codes[] = $step['send'];
                foreach ($step['choose'] ?? [] as $choice) {
                    $codes[] = $choice[1];
                }
                foreach ($step['alternatives'] ?? [] as $alternative) {
                    $codes[] = $alternative['code'];
                }
            }
        }

        $expected = ['AB1', 'AB2', 'AB3', 'AB4', 'OP3', 'G01', 'P06', 'G05', 'PB7', 'D07', 'RM1', 'N35', 'T05', 'L18', 'N05', 'N05-50', 'N01', 'N01-S', 'L12', 'N41'];
        $this->assertEqualsCanonicalizing($expected, $codes);
        $this->assertSame(array_unique($codes), $codes);   // none twice
        $this->assertNotContains('X99', $codes);             // switched off
    }

    public function test_the_newsletter_is_one_scheduled_automation_per_issue_with_its_variants(): void
    {
        $flows = EventPlan::flows($this->eventCatalogue());

        $january = $this->flowOf($flows, 'CL · Newsletter · janvier n°1 · L18');
        $this->assertSame('schedule', $january['trigger']['kind']);
        $this->assertSame([1], $january['trigger']['config']['months']);
        $this->assertSame([8], $january['trigger']['config']['days']);              // first issue
        $this->assertSame('11:00', $january['trigger']['config']['at']);
        $this->assertSame(60, $january['trigger']['config']['window_minutes']);
        $this->assertSame([['label' => 'CL · Tous les contacts (newsletter)'], ['field' => 'mailing_optout', 'is_not' => 'oui']], $january['trigger']['config']['items']);
        $this->assertSame(['skip'], [$january['steps'][0]['extra']['if_busy']]);     // a day late is not sent

        $this->assertSame([22], $this->flowOf($flows, 'CL · Newsletter · février n°2 · N05')['trigger']['config']['days']);

        // The variants are the same automation: the email that replaces another for some contacts
        $february = $this->flowOf($flows, 'CL · Newsletter · février n°2 · N05');
        $this->assertSame([['field' => 'plus_50', 'is' => 'oui', 'code' => 'N05-50', 'label' => 'Le contact a 50 ans ou plus ?']], $february['steps'][0]['alternatives']);
        $this->assertSame('sans_correction', $this->flowOf($flows, 'CL · Newsletter · juillet n°1 · N01')['steps'][0]['alternatives'][0]['field']);
        $this->assertNotContains('CL · Newsletter · février n°2 · N05-50', array_column($flows, 'name'));

        // Counted from the request, and a reserve issue that waits for a person
        $this->assertSame('created', $this->flowOf($flows, 'CL · 6 mois après la demande · L12')['trigger']['kind']);
        $this->assertSame('label', $this->flowOf($flows, 'CL · Envoi à la demande · N41')['trigger']['kind']);
    }

    public function test_the_emails_around_an_appointment_wait_until_their_day_and_stop_when_it_has_passed(): void
    {
        $flows = EventPlan::flows($this->eventCatalogue());

        $ab3 = $this->flowOf($flows, 'CL · Avant le bilan · AB3');
        $this->assertSame(['kind' => 'label', 'label' => 'CL · Bilan fixé'], $ab3['trigger']);
        $this->assertSame(['relative_to' => 'next_event', 'offset_days' => 7, 'direction' => 'before', 'at' => '09:00', 'if_past' => 'end'], $ab3['steps'][0]['wait']);

        // The hesitating ones only
        $ab4 = $this->flowOf($flows, 'CL · Avant le bilan · AB4');
        $this->assertArrayHasKey('require', $ab4['steps'][0]);
        $this->assertSame('hesitation', $ab4['steps'][0]['require']['items'][0]['field']);

        // The day after, if the appointment is still 2 days away
        $ab2 = $this->flowOf($flows, 'CL · Avant le bilan · AB2');
        $this->assertSame(['days' => 1], $ab2['steps'][0]['wait']);
        $this->assertTrue($ab2['steps'][1]['wait']['check_only']);

        // After the operation: the next appointment of the stage, plus 1 day
        $this->assertSame(['after', 1], [$this->flowOf($flows, 'CL · Après l\'intervention · OP3')['steps'][0]['wait']['direction'], $this->flowOf($flows, 'CL · Après l\'intervention · OP3')['steps'][0]['wait']['offset_days']]);
        $g01 = $this->flowOf($flows, 'CL · Après l\'intervention · G01');
        $this->assertSame('or', $g01['steps'][1]['require']['op']);                     // a score of 4 or 5
        $this->assertSame(['P06', 'G05'], array_column(array_filter($this->flowOf($flows, 'CL · Un an après l\'intervention · P06')['steps'], fn ($s) => isset($s['send'])), 'send'));

        // The day after the bilan, by the decision taken
        $decision = $this->flowOf($flows, 'CL · Lendemain du bilan · décision');
        $this->assertSame(['PB7', 'D07'], array_column($decision['steps'][1]['choose'], 1));
        $this->assertSame(['field' => 'decision_bilan', 'is' => 'refus'], $decision['steps'][1]['choose'][1][0]);
    }

    public function test_the_others_leave_when_their_label_is_put_and_the_label_is_then_taken_off(): void
    {
        $flows = EventPlan::flows($this->eventCatalogue());

        $n35 = $this->flowOf($flows, 'CL · Envoi à la demande · N35');
        $this->assertSame(['kind' => 'label', 'label' => 'Envoi · N35'], $n35['trigger']);
        $this->assertSame([['send' => 'N35'], ['detach_label' => 'Envoi · N35', 'name' => 'Retirer l\'étiquette d\'envoi']], $n35['steps']);

        // 30 minutes after a missed appointment
        $this->assertSame(['minutes' => 30], $this->flowOf($flows, 'CL · Envoi à la demande · RM1')['steps'][0]['wait']);
    }

    public function test_the_whole_plan_becomes_drafts_that_pass_the_validation(): void
    {
        $dir = sys_get_temp_dir() . '/plan-' . uniqid();
        mkdir($dir, 0777, true);
        file_put_contents("$dir/catalogue.json", json_encode($this->eventCatalogue()));
        foreach (array_column($this->eventCatalogue()['messages'], 'code') as $code) {
            $this->template(['code' => $code, 'name' => "CL $code"]);
        }

        $report = app(PlanGenerator::class)->generate(1, $dir);

        $this->assertNotEmpty($report['workflows']);
        foreach ($report['workflows'] as $name => $workflow) {
            $this->assertSame([], $workflow['problems'], $name);   // triggers, waits, tests and emails are all known
            $this->assertSame('créée', $workflow['status']);
        }

        // The labels: the list, the stages, and one per email on demand
        $categories = DB::table('categories')->pluck('name')->all();
        $this->assertEqualsCanonicalizing(['Parcours de mailing', 'Étapes du parcours', 'Envois à la demande'], $categories);
        $this->assertContains('Envoi · N35', DB::table('labels')->pluck('name')->all());
        $this->assertContains('CL · Bilan fixé', DB::table('labels')->pluck('name')->all());

        // Nothing starts by itself
        $this->assertSame(0, Workflow::where('status', '!=', 'draft')->count());
        $this->assertSame(0, WorkflowExecution::count());
    }

    public function test_the_confirmation_of_a_deletion_is_the_one_email_that_leaves_after_the_request(): void
    {
        $this->setRules();
        $this->template(['code' => 'T05', 'marketing' => 0]);
        $this->setMeta(1, ['suppression_demandee' => 'oui', 'mailing_optout' => 'oui']);
        $this->activate($this->graph(['action' => 'send_email', 'provider' => 'email:project', 'thread' => 1, 'template' => 'T05']));

        $this->fire(1);

        $this->assertSame(1, Message::count());
        $this->assertArrayNotHasKey('skipped', $this->nodeRow('a1')->result);
    }

    public function test_a_dry_run_of_the_plan_writes_nothing_and_a_missing_template_is_reported(): void
    {
        $dir = $this->miniKit();
        DB::table('message_templates')->where('code', 'PC3')->delete();

        $report = app(PlanGenerator::class)->generate(1, $dir, true);

        $this->assertSame(0, Workflow::count());
        $this->assertSame(0, DB::table('labels')->count());
        $this->assertContains('Le modèle d\'email de l\'action "PC3 · Objet de PC3" n\'existe plus dans ce projet.', $report['workflows']['CL · Premier contact']['problems']);
    }

    public function test_a_message_written_by_a_workflow_is_queued_at_once(): void
    {
        // A queue worker has no "response": what waits for it is never sent
        Queue::fake();
        $message = Message::first() ?: Message::withoutEvents(fn () => Message::forceCreate(['prospect_id' => 1, 'thread_id' => 1, 'from_user' => 1, 'body' => 'x']));

        WorkflowTriggerResolver::withoutTriggers(fn () => (new MessageObserver())->created($message));

        Queue::assertPushed(ProspectThreadMessage::class, 1);
    }

    public function test_a_message_written_in_a_request_still_waits_for_the_response(): void
    {
        Queue::fake();
        $message = Message::withoutEvents(fn () => Message::forceCreate(['prospect_id' => 1, 'thread_id' => 1, 'from_user' => 1, 'body' => 'x']));

        (new MessageObserver())->created($message);

        Queue::assertNotPushed(ProspectThreadMessage::class);
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

    public function test_prospect_call_observer_does_not_duplicate_an_agent_call_workflow(): void
    {
        \Illuminate\Support\Facades\Schema::table('projects', function (Blueprint $table): void {
            $table->unsignedBigInteger('call_agent_id')->nullable();
        });
        DB::table('projects')->where('id', 1)->update(['call_agent_id' => 7]);
        $workflowId = DB::table('workflows')->insertGetId([
            'project_id' => 1,
            'name' => 'Agent call on creation',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $versionId = DB::table('workflow_versions')->insertGetId([
            'workflow_id' => $workflowId,
            'version' => 1,
            'trigger_type' => 'prospect.created',
            'graph' => json_encode($this->graph(['action' => 'agent_call'])),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('workflows')->where('id', $workflowId)->update(['active_version_id' => $versionId]);
        WorkflowTriggerResolver::forgetIndex();
        Queue::fake();

        (new ProspectCallObserver())->created(new Prospect(['project_id' => 1]));

        Queue::assertNotPushed(PrepareAutomaticCall::class);
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

        foreach (['send_email', 'send_sms', 'make_call', 'agent_call', 'agent_sms'] as $type) {
            $this->assertNotNull($registry->get($type));
            $this->assertTrue($registry->has($type, 'prospect'));
        }

        // The campaign actions are reused as they are
        $this->assertTrue($registry->has('prospect-attach-label', 'prospect'));
    }

    public function test_agent_communication_actions_are_catalogued_as_human_activated_real_messages(): void
    {
        $catalog = app(\App\Workflows\WorkflowCatalog::class)->build();
        $actions = collect($catalog['actions'])->keyBy('type');

        foreach (['agent_call', 'agent_sms'] as $type) {
            $this->assertArrayHasKey($type, $actions);
            $this->assertStringContainsString('véritable', $actions[$type]['description']);
            $this->assertStringContainsString('activation humaine', $actions[$type]['description']);
            $fields = array_column($actions[$type]['fields'], 'key');
            $this->assertContains('agent_id', $fields);
            $this->assertContains('instruction', $fields);
            $this->assertContains('documents', $fields);
            $this->assertContains('on_error', $fields);
        }
        $this->assertContains('provider', array_column($actions['agent_sms']['fields'], 'key'));
    }

    public function test_resolving_the_registry_does_not_need_the_test_double(): void
    {
        // Sanity check of the test setup: this file never replaces the registry
        $this->assertInstanceOf(ActionRegistry::class, app(ActionRegistry::class));
        $this->assertSame([], RecordingHandler::$calls);
    }
}
