<?php

namespace Tests\Feature;

use App\Console\Commands\Campaign as CampaignCommand;
use App\Jobs\Workflow\EvaluateWorkflowTriggers;
use App\Models\Campaign;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Workflows\Actions\ActionRegistry;
use App\Workflows\ConditionEvaluator;
use App\Workflows\Legacy\LegacyCampaignConverter;
use App\Workflows\Legacy\LegacyConversionException;
use App\Workflows\WorkflowValidationException;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Tests\Support\RecordingHandler;
use Tests\TestCase;

/**
 * Conversion of the legacy campaigns to workflows.
 *
 * The heart of it is the equivalence test: a campaign and the workflow made
 * from it must select exactly the same prospects, whatever the shape of the
 * rules (AND / OR operators, nested, "count" rules).
 */
class WorkflowLegacyConversionTest extends TestCase
{
    protected LegacyCampaignConverter $converter;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'workflow_legacy_test',
            'database.connections.workflow_legacy_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
            'queue.default' => 'sync',
            'campaign.timezone' => 'UTC',
        ]);

        foreach ([
            '2026_10_30_000001_create_workflow_tables',
            '2026_10_30_000002_add_workflow_filter_memberships',
            '2026_10_30_000003_add_waiting_event_to_workflow_executions',
            '2026_10_30_000004_add_draft_graph_to_workflows',
            '2026_10_30_000005_add_legacy_campaign_to_workflows',
        ] as $migration) {
            (include base_path("database/migrations/$migration.php"))->up();
        }

        $this->legacySchema();

        RecordingHandler::$calls = [];
        $registry = app(ActionRegistry::class);
        // The campaign actions are reused by name; here they only record what they are asked
        $registry->register('prospect-processed', new RecordingHandler('processed'));
        $registry->register('prospect-detach-user', new RecordingHandler('detach'));

        $this->converter = app(LegacyCampaignConverter::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    protected function legacySchema(): void
    {
        $schema = DB::connection()->getSchemaBuilder();

        $schema->create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->softDeletes();
        });
        $schema->create('project_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->string('key');
            $table->text('value')->nullable();
        });
        $schema->create('prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        $schema->create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(false);
            $table->boolean('pending')->default(false);
            $table->boolean('processing')->default(false);
            $table->boolean('re_process_processed_prospects')->default(false);
            $table->boolean('error')->default(false);
            $table->string('for')->default('prospect');
            $table->string('frequency')->default('daily');
            $table->timestamp('date')->nullable();
            $table->timestamp('begin_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->text('style')->nullable();
            $table->unsignedInteger('execution')->default(0);
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        $schema->create('campaign_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->default(1);
            $table->string('name')->nullable();
            $table->string('type')->default('filter');
            $table->text('rule');
            $table->text('style')->nullable();
        });
        $schema->create('campaign_operators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->default(1);
            $table->boolean('and')->default(true);
            $table->text('style')->nullable();
        });
        $schema->create('campaign_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->default(1);
            $table->string('name')->nullable();
            $table->string('action');
            $table->text('value');
            $table->text('style')->nullable();
        });
        $schema->create('campaign_rule', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('rule_id');
            $table->boolean('error')->default(false);
        });
        $schema->create('campaign_operator', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('operator_id');
        });
        $schema->create('campaign_rule_operator', function (Blueprint $table) {
            $table->unsignedBigInteger('rule_id');
            $table->unsignedBigInteger('operator_id');
            $table->boolean('error')->default(false);
        });
        $schema->create('campaign_operator_operator', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id');
            $table->unsignedBigInteger('child_id');
        });
        $schema->create('campaign_action', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('action_id');
            $table->boolean('processing')->default(false);
            $table->boolean('error')->default(false);
            $table->timestamp('begin_at')->nullable();
            $table->timestamp('end_at')->nullable();
        });
        $schema->create('campaign_prospect', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('action_id');
            $table->boolean('processing')->default(false);
            $table->boolean('error')->default(false);
            $table->unsignedInteger('execution')->default(1);
        });

        DB::table('projects')->insert([['id' => 1, 'name' => 'Heroes'], ['id' => 2, 'name' => 'Other']]);

        // 8 prospects. archived: 2, 4, 6. created recently: 1-4 (the others 60 days ago).
        // updated recently: all but 8.
        $rows = [];
        foreach (range(1, 8) as $id) {
            $rows[] = [
                'id' => $id,
                'project_id' => 1,
                'processed_at' => in_array($id, [2, 4, 6]) ? now()->subDay()->toDateTimeString() : null,
                'created_at' => ($id <= 4 ? now()->subDay() : now()->subDays(60))->toDateTimeString(),
                'updated_at' => ($id == 8 ? now()->subDays(60) : now()->subDay())->toDateTimeString(),
            ];
        }
        DB::table('prospects')->insert($rows);
        DB::table('prospects')->insert(['id' => 9, 'project_id' => 2, 'created_at' => now()->toDateTimeString(), 'updated_at' => now()->toDateTimeString()]);
    }

    // Helpers

    protected int $ruleSeq = 0;

    protected function rule(array $rule, string $type = 'filter'): int
    {
        return DB::table('campaign_rules')->insertGetId(['type' => $type, 'rule' => json_encode($rule), 'name' => 'R' . ++$this->ruleSeq]);
    }

    protected function operator(bool $and, array $rules = [], array $children = []): int
    {
        $id = DB::table('campaign_operators')->insertGetId(['and' => $and]);

        foreach ($rules as $rule) {
            DB::table('campaign_rule_operator')->insert(['rule_id' => $rule, 'operator_id' => $id]);
        }
        foreach ($children as $child) {
            DB::table('campaign_operator_operator')->insert(['parent_id' => $id, 'child_id' => $child]);
        }

        return $id;
    }

    protected function campaign(array $rules = [], array $operators = [], array $attributes = [], array $actions = ['prospect-processed']): Campaign
    {
        $id = DB::table('campaigns')->insertGetId($attributes + [
            'project_id' => 1, 'name' => 'Relance', 'for' => 'prospect', 'frequency' => 'daily',
            'active' => true, 'date' => now()->subDays(5)->toDateTimeString(), 'creator_id' => 7,
        ]);

        foreach ($rules as $rule) {
            DB::table('campaign_rule')->insert(['campaign_id' => $id, 'rule_id' => $rule]);
        }
        foreach ($operators as $operator) {
            DB::table('campaign_operator')->insert(['campaign_id' => $id, 'operator_id' => $operator]);
        }
        foreach ($actions as $action) {
            $actionId = DB::table('campaign_actions')->insertGetId(['action' => $action, 'value' => json_encode([])]);
            DB::table('campaign_action')->insert(['campaign_id' => $id, 'action_id' => $actionId]);
        }

        return Campaign::find($id);
    }

    /**
     * Prospects the campaign selects (its own query)
     */
    protected function legacySelection(Campaign $campaign): array
    {
        return $campaign->matching()->pluck('prospects.id')->map(fn ($id) => (int) $id)->sort()->values()->all();
    }

    /**
     * Prospects the converted condition selects, prospect by prospect (what the engine does)
     */
    protected function workflowSelection(Campaign $campaign): array
    {
        $tree = $this->converter->tree($campaign);
        $evaluator = app(ConditionEvaluator::class);

        return collect(range(1, 8))
            ->filter(fn ($id) => $evaluator->matches(1, 'prospect', $id, $tree))
            ->values()
            ->all();
    }

    // Equivalence: the same prospects

    public function test_a_single_rule_selects_the_same_prospects(): void
    {
        $campaign = $this->campaign([$this->rule(['processed' => false])]);

        $this->assertSame([1, 3, 5, 7, 8], $this->legacySelection($campaign));
        $this->assertSame($this->legacySelection($campaign), $this->workflowSelection($campaign));
    }

    public function test_the_filters_of_a_rule_are_combined_with_and(): void
    {
        $campaign = $this->campaign([$this->rule(['processed' => true, 'createdDaysAgo' => 7])]);

        $this->assertSame([2, 4], $this->legacySelection($campaign));
        $this->assertSame($this->legacySelection($campaign), $this->workflowSelection($campaign));
    }

    public function test_the_rules_and_operators_of_a_campaign_are_alternatives(): void
    {
        // archived OR (recent AND not archived)
        $campaign = $this->campaign(
            [$this->rule(['processed' => true])],
            [$this->operator(true, [$this->rule(['createdDaysAgo' => 7]), $this->rule(['processed' => false])])]
        );

        $this->assertSame([1, 2, 3, 4, 6], $this->legacySelection($campaign));
        $this->assertSame($this->legacySelection($campaign), $this->workflowSelection($campaign));
    }

    public function test_nested_operators_select_the_same_prospects(): void
    {
        // AND( not archived, OR( recent, updated recently and archived ) )
        $inner = $this->operator(false, [$this->rule(['createdDaysAgo' => 7]), $this->rule(['processed' => true, 'updatedDaysAgo' => 30])]);
        $outer = $this->operator(true, [$this->rule(['processed' => false])], [$inner]);
        $campaign = $this->campaign([], [$outer]);

        $this->assertSame([1, 3], $this->legacySelection($campaign));
        $this->assertSame($this->legacySelection($campaign), $this->workflowSelection($campaign));
    }

    public function test_count_rules_restrict_the_whole_selection(): void
    {
        // not archived, but only what was updated in the last 30 days (prospect 8 is not)
        $campaign = $this->campaign([$this->rule(['processed' => false]), $this->rule(['updatedDaysAgo' => 30], 'count')]);

        $this->assertSame([1, 3, 5, 7], $this->legacySelection($campaign));
        $this->assertSame($this->legacySelection($campaign), $this->workflowSelection($campaign));
    }

    public function test_empty_rules_and_operators_restrict_nothing_as_in_a_campaign(): void
    {
        $campaign = $this->campaign(
            [$this->rule(['processed' => true]), $this->rule([])],
            [$this->operator(true), $this->operator(false, [$this->rule([])])]
        );

        $this->assertSame([2, 4, 6], $this->legacySelection($campaign));
        $this->assertSame($this->legacySelection($campaign), $this->workflowSelection($campaign));
        // ... and they are not carried into the new condition
        $this->assertSame(['op' => 'and', 'items' => [['op' => 'or', 'items' => [['filters' => ['processed' => true]]]]]], $this->converter->tree($campaign));
    }

    public function test_a_cycle_of_operators_does_not_loop_forever(): void
    {
        $a = $this->operator(true, [$this->rule(['processed' => true])]);
        $b = $this->operator(true, [], [$a]);
        DB::table('campaign_operator_operator')->insert(['parent_id' => $a, 'child_id' => $b]);
        $campaign = $this->campaign([], [$a]);

        $this->assertSame(['op' => 'and', 'items' => [['op' => 'or', 'items' => [['op' => 'and', 'items' => [['filters' => ['processed' => true]]]]]]]], $this->converter->tree($campaign));
    }

    // What can be converted

    public function test_each_campaign_is_analysed_with_its_reason(): void
    {
        $rule = $this->rule(['processed' => false]);
        $status = fn (Campaign $campaign) => $this->converter->analyze($campaign)['status'];

        $this->assertSame('convertible', $status($this->campaign([$rule])));
        $this->assertSame('manual', $status($this->campaign([$rule], [], ['for' => 'order'])));
        $this->assertSame('manual', $status($this->campaign([$rule], [], ['for' => 'event'])));
        $this->assertSame('manual', $status($this->campaign([$rule], [], ['re_process_processed_prospects' => true])));
        $this->assertSame('manual', $status($this->campaign([$rule], [], ['frequency' => 'once', 'execution' => 0])));
        $this->assertSame('finished', $status($this->campaign([$rule], [], ['frequency' => 'once', 'execution' => 1])));
        $this->assertSame('manual', $status($this->campaign([$rule], [], [], [])));                 // no action
        $this->assertSame('manual', $status($this->campaign([$rule], [], [], ['no-such-action']))); // unknown action
        $this->assertSame('manual', $status($this->campaign([])));                                  // no rule
        $this->assertSame('manual', $status($this->campaign([$this->rule([])])));                   // only an empty rule
    }

    public function test_the_reason_a_campaign_stays_is_explained_in_plain_language(): void
    {
        $rule = $this->rule(['processed' => false]);

        $this->assertStringContainsString('les devis', $this->converter->analyze($this->campaign([$rule], [], ['for' => 'order']))['reasons'][0]);
        $this->assertStringContainsString('retraite les prospects déjà traités', $this->converter->analyze($this->campaign([$rule], [], ['re_process_processed_prospects' => true]))['reasons'][0]);
        $this->assertStringContainsString('no-such-action', $this->converter->analyze($this->campaign([$rule], [], [], ['no-such-action']))['reasons'][0]);
    }

    public function test_the_differences_are_announced_for_a_convertible_campaign(): void
    {
        $analysis = $this->converter->analyze($this->campaign([$this->rule(['createdDaysAgo' => 7])]));

        $this->assertSame('convertible', $analysis['status']);
        $this->assertCount(4, $analysis['notes']);   // continuous, once per prospect, backfill, clock criteria
        $this->assertStringContainsString('dépendent du temps', implode(' ', $analysis['notes']));
    }

    // The workflow made from a campaign

    public function test_the_campaign_becomes_a_published_draft_workflow(): void
    {
        $campaign = $this->campaign([$this->rule(['processed' => false])], [], ['name' => 'Relance J+2'], ['prospect-processed', 'prospect-detach-user']);

        $workflow = $this->converter->convert($campaign, 7);

        $this->assertSame('Campagne : Relance J+2', $workflow->name);
        $this->assertSame($campaign->id, $workflow->legacy_campaign_id);
        $this->assertSame(Workflow::DRAFT, $workflow->status);             // nothing runs before the cutover
        $this->assertNotNull($workflow->active_version_id);
        $this->assertSame('prospect.filter_entered', $workflow->activeVersion->trigger_type);
        $this->assertTrue((bool) $campaign->fresh()->active);                      // the campaign is untouched
    }

    public function test_the_actions_keep_their_order_and_their_values(): void
    {
        $campaign = $this->campaign([$this->rule(['processed' => false])], [], [], []);
        foreach ([['prospect-processed', []], ['prospect-detach-user', ['user' => 12]]] as [$action, $value]) {
            $id = DB::table('campaign_actions')->insertGetId(['action' => $action, 'value' => json_encode($value)]);
            DB::table('campaign_action')->insert(['campaign_id' => $campaign->id, 'action_id' => $id]);
        }

        $graph = $this->converter->graph($campaign);
        $nodes = collect($graph['nodes'])->keyBy('id');

        $this->assertSame(['trigger', 'action1', 'action2', 'end'], array_keys($nodes->all()));
        $this->assertSame('prospect-processed', $nodes['action1']['config']['action']);
        $this->assertSame(['user' => 12], $nodes['action2']['config']['value']);
        $this->assertSame('continue', $nodes['action2']['config']['on_error']);   // as the campaign carries on after a failure
        $this->assertTrue($nodes['trigger']['config']['once_per_subject']);
        $this->assertSame([['from' => 'trigger', 'to' => 'action1'], ['from' => 'action1', 'to' => 'action2'], ['from' => 'action2', 'to' => 'end']], $graph['edges']);
    }

    public function test_a_campaign_that_starts_later_waits_for_its_date_and_checks_the_rules_again(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00');
        $campaign = $this->campaign([$this->rule(['processed' => false])], [], ['date' => '2026-11-15 08:30:00']);

        $graph = $this->converter->graph($campaign);
        $nodes = collect($graph['nodes'])->keyBy('id');

        $this->assertSame(['trigger', 'wait', 'check', 'action1', 'end', 'end_no'], array_keys($nodes->all()));
        $this->assertSame(['until_date' => '2026-11-15 08:30', 'timezone' => 'UTC'], $nodes['wait']['config']);
        $this->assertSame($nodes['trigger']['config']['items'], $nodes['check']['config']['items']);
        $this->assertContains(['from' => 'check', 'to' => 'action1', 'branch' => 'yes'], $graph['edges']);
        $this->assertContains(['from' => 'check', 'to' => 'end_no', 'branch' => 'no'], $graph['edges']);

        // ... and it is a valid workflow
        $this->assertNotNull($this->converter->convert($campaign)->active_version_id);
    }

    public function test_a_campaign_is_converted_once(): void
    {
        $campaign = $this->campaign([$this->rule(['processed' => false])]);
        $workflow = $this->converter->convert($campaign);

        $this->assertSame('converted', $this->converter->analyze($campaign)['status']);
        $this->assertSame($workflow->id, $this->converter->analyze($campaign)['workflow_id']);

        $this->expectException(LegacyConversionException::class);
        $this->converter->convert($campaign);
    }

    public function test_a_campaign_with_an_invalid_rule_leaves_nothing_behind_and_can_be_retried(): void
    {
        $bad = $this->rule(['noSuchFilter' => 1]);
        $campaign = $this->campaign([$bad]);

        try {
            $this->converter->convert($campaign);
            $this->fail('The conversion must be refused');
        } catch (LegacyConversionException $e) {
            $this->assertStringContainsString('noSuchFilter', $e->getMessage());
        }

        $this->assertSame(0, Workflow::withTrashed()->count());

        // The rule is fixed: the same campaign converts
        DB::table('campaign_rules')->where('id', $bad)->update(['rule' => json_encode(['processed' => false])]);
        $this->assertNotNull($this->converter->convert($campaign->fresh()));
    }

    // Handing over

    protected function processedBy(Campaign $campaign, array $prospectIds): void
    {
        $actionId = $campaign->actions()->orderBy('campaign_action.id')->first()->id;

        foreach ($prospectIds as $id) {
            DB::table('campaign_prospect')->insert(['campaign_id' => $campaign->id, 'prospect_id' => $id, 'action_id' => $actionId]);
        }
    }

    public function test_the_cutover_hands_over_the_prospects_the_campaign_had_not_processed(): void
    {
        $campaign = $this->campaign([$this->rule(['processed' => false])]);   // matches 1, 3, 5, 7, 8
        $this->processedBy($campaign, [1, 5]);                                // the campaign did these two already
        $workflow = $this->converter->convert($campaign);

        $this->converter->cutover($campaign, $workflow);

        $this->assertSame(Workflow::ACTIVE, $workflow->fresh()->status);
        $this->assertFalse((bool) $campaign->fresh()->active);                       // the scheduler stops running it
        $this->assertEqualsCanonicalizing(['processed:3', 'processed:7', 'processed:8'], RecordingHandler::$calls);
        $this->assertSame(3, WorkflowExecution::where('status', 'completed')->count());
    }

    public function test_handing_over_twice_does_not_process_anybody_twice(): void
    {
        $campaign = $this->campaign([$this->rule(['processed' => false])]);
        $workflow = $this->converter->convert($campaign);

        $this->converter->cutover($campaign, $workflow);
        $calls = RecordingHandler::$calls;
        $this->converter->cutover($campaign->fresh(), $workflow->fresh());

        $this->assertSame($calls, RecordingHandler::$calls);
        $this->assertSame(5, WorkflowExecution::count());
    }

    public function test_a_prospect_is_processed_once_even_if_it_leaves_and_enters_the_filter_again(): void
    {
        // The rule: archived prospects. Prospects 2, 4 and 6 are archived and already inside.
        $campaign = $this->campaign([$this->rule(['processed' => true])]);
        $this->processedBy($campaign, [2, 4, 6]);
        $workflow = $this->converter->convert($campaign);
        $this->converter->cutover($campaign, $workflow);
        $this->assertSame([], RecordingHandler::$calls);                      // all done by the campaign

        // Prospect 1 becomes archived: it enters the filter
        DB::table('prospects')->where('id', 1)->update(['processed_at' => now()]);
        EvaluateWorkflowTriggers::dispatchSync('prospect', [1], 1, 'updated', 1000);
        $this->assertSame(['processed:1'], RecordingHandler::$calls);

        // ... leaves, and enters again: the campaign would not process it twice
        DB::table('prospects')->where('id', 1)->update(['processed_at' => null]);
        EvaluateWorkflowTriggers::dispatchSync('prospect', [1], 1, 'updated', 2000);
        DB::table('prospects')->where('id', 1)->update(['processed_at' => now()]);
        EvaluateWorkflowTriggers::dispatchSync('prospect', [1], 1, 'updated', 3000);

        $this->assertSame(['processed:1'], RecordingHandler::$calls);
        $this->assertSame(1, WorkflowExecution::where('subject_id', 1)->count());
    }

    public function test_the_campaign_keeps_running_if_the_workflow_cannot_be_activated(): void
    {
        $campaign = $this->campaign([$this->rule(['processed' => false])]);
        $workflow = $this->converter->convert($campaign);
        // Something changed since: the action does not exist anymore
        DB::table('workflow_versions')->where('workflow_id', $workflow->id)->update([
            'graph' => json_encode(['nodes' => [
                ['id' => 'trigger', 'type' => 'trigger', 'config' => ['trigger' => 'prospect.filter_entered', 'items' => [['filters' => ['processed' => false]]]]],
                ['id' => 'action1', 'type' => 'action', 'config' => ['action' => 'zzz']],
            ], 'edges' => [['from' => 'trigger', 'to' => 'action1']]]),
        ]);

        try {
            $this->converter->cutover($campaign, $workflow->fresh());
            $this->fail('The cutover must be refused');
        } catch (WorkflowValidationException $e) {
            $this->assertNotEmpty($e->errors);
        }

        $this->assertTrue((bool) $campaign->fresh()->active);
        $this->assertSame(Workflow::DRAFT, $workflow->fresh()->status);
        $this->assertSame(0, WorkflowExecution::count());
    }

    // The legacy engine is left alone

    protected function campaignsRunBy(string $frequency): array
    {
        $command = new class extends CampaignCommand {
            public array $handled = [];

            public function handleCampaign(Campaign $campaign)
            {
                $this->handled[] = $campaign->id;
            }
        };
        $command->setLaravel($this->app);
        $command->run(new ArrayInput(['--frequency' => $frequency]), new NullOutput());

        return $command->handled;
    }

    public function test_the_scheduler_runs_the_campaigns_until_the_cutover_and_not_after(): void
    {
        $converted = $this->campaign([$this->rule(['processed' => false])]);
        $other = $this->campaign([$this->rule(['processed' => true])], [], ['name' => 'Autre']);
        $workflow = $this->converter->convert($converted);

        // Converted but not handed over: still run by the scheduler, as before
        $this->assertEqualsCanonicalizing([$converted->id, $other->id], $this->campaignsRunBy('daily'));

        $this->converter->cutover($converted, $workflow);

        $this->assertSame([$other->id], $this->campaignsRunBy('daily'));
    }

    // Commands

    public function test_the_report_lists_the_campaigns_and_changes_nothing(): void
    {
        $rule = $this->rule(['processed' => false]);
        $this->campaign([$rule], [], ['name' => 'Convertible']);
        $this->campaign([$rule], [], ['name' => 'Devis', 'for' => 'order']);

        $this->artisan('workflows:campaigns', ['--notes' => true])
            ->expectsOutputToContain('convertible')
            ->expectsOutputToContain('reste en campagne')
            ->expectsOutputToContain('Campagne sur les devis')
            ->expectsOutputToContain('Chaque prospect n\'est traité qu\'une fois')
            ->assertExitCode(0);

        $this->assertSame(0, Workflow::count());
        $this->assertSame(2, Campaign::where('active', true)->count());
    }

    public function test_converting_without_activation_leaves_the_campaign_running(): void
    {
        $campaign = $this->campaign([$this->rule(['processed' => false])]);

        $this->artisan('workflows:convert-campaign', ['campaign' => $campaign->id])
            ->expectsOutputToContain('created from the campaign')
            ->assertExitCode(0);

        $this->assertSame(Workflow::DRAFT, Workflow::first()->status);
        $this->assertTrue((bool) $campaign->fresh()->active);
        $this->assertSame([], RecordingHandler::$calls);
    }

    public function test_converting_with_activation_hands_the_campaign_over(): void
    {
        $campaign = $this->campaign([$this->rule(['processed' => false])]);

        $this->artisan('workflows:convert-campaign', ['campaign' => $campaign->id, '--activate' => true])
            ->expectsOutputToContain('switched off')
            ->assertExitCode(0);

        $this->assertSame(Workflow::ACTIVE, Workflow::first()->status);
        $this->assertFalse((bool) $campaign->fresh()->active);
        $this->assertCount(5, RecordingHandler::$calls);

        // Running it again changes nothing
        $this->artisan('workflows:convert-campaign', ['campaign' => $campaign->id, '--activate' => true])
            ->expectsOutputToContain('already converted')
            ->assertExitCode(0);
        $this->assertCount(5, RecordingHandler::$calls);
        $this->assertSame(1, Workflow::count());
    }

    public function test_a_campaign_that_cannot_be_converted_is_refused_with_the_reason(): void
    {
        $campaign = $this->campaign([$this->rule(['processed' => false])], [], ['for' => 'order']);

        $this->artisan('workflows:convert-campaign', ['campaign' => $campaign->id, '--activate' => true])
            ->expectsOutputToContain('cannot be converted')
            ->expectsOutputToContain('les devis')
            ->assertExitCode(1);

        $this->assertTrue((bool) $campaign->fresh()->active);
        $this->artisan('workflows:convert-campaign', ['campaign' => 999])->expectsOutputToContain('not found')->assertExitCode(1);
    }

    public function test_every_action_of_the_campaigns_has_a_label_in_the_interface(): void
    {
        $lang = json_decode(file_get_contents(base_path('resources/js/lang/fr.json')), true)['workflow']['action_type'];
        $names = collect(app(CampaignCommand::class)->getActionHandlers())->flatMap(fn ($handlers) => array_keys($handlers))->unique();

        $missing = $names->reject(fn ($name) => isset($lang[preg_replace('/[.-]/', '_', $name)]))->values()->all();

        $this->assertSame([], $missing);
    }
}
