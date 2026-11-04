<?php

namespace Tests\Feature;

use App\Agents\AgentConversationService;
use App\Agents\AgentExecutionContext;
use App\Agents\AgentRunner;
use App\Agents\Claude\ClaudeGatewayFactory;
use App\Agents\Entities\AgentReadException;
use App\Agents\Entities\ReadGateway;
use App\Agents\Entities\StatsGateway;
use App\Http\Middleware\UserTrackActivity;
use App\Models\Agent;
use App\Models\AgentMessage;
use App\Models\AgentRunStep;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\ScriptedGateway;
use Tests\Support\ScriptedGatewayFactory;
use Tests\Support\CrmFixtureTestCase;

/**
 * The read tools on the REAL tables of the CRM (MySQL), inside a transaction
 * that is rolled back: AGENTS_TEST_DB=mysql php vendor/bin/phpunit --filter AgentReadToolsTest
 * (the data is described in Tests\Support\CrmFixtureTestCase).
 */
class AgentReadToolsTest extends CrmFixtureTestCase
{
    // Prospects

    public function test_an_administrator_sees_the_prospects_of_the_project_and_only_those(): void
    {
        $result = $this->search('admin', 'prospect');

        $this->assertSame(5, $result['total']);
        $this->assertSame($this->expected('p1', 'p2', 'p3', 'p4', 'p5'), $this->ids($result));
        $this->assertNotContains($this->p['q1'], $this->ids($result));
    }

    public function test_a_telepro_sees_what_the_interface_shows_the_ones_created_assigned_or_in_a_group(): void
    {
        $result = $this->search('t1', 'prospect');

        $this->assertSame($this->expected('p1', 'p2', 'p3'), $this->ids($result));
        $this->assertSame(3, $result['total']);
    }

    public function test_a_supervisor_with_the_right_to_see_every_prospect_sees_them_all(): void
    {
        $this->assertSame($this->expected('p1', 'p2', 'p3', 'p4', 'p5'), $this->ids($this->search('t3', 'prospect')));
    }

    public function test_a_member_without_any_role_sees_only_what_is_theirs(): void
    {
        $this->assertSame([], $this->ids($this->search('bare', 'prospect')));
    }

    public function test_another_project_never_leaks_even_when_asked_for_by_filter(): void
    {
        $result = $this->search('admin', 'prospect', ['filters' => ['query' => 'Etranger']]);

        $this->assertSame(0, $result['total']);
    }

    public function test_the_filters_mean_what_they_mean_in_the_interface(): void
    {
        $this->assertSame($this->expected('p1'), $this->ids($this->search('admin', 'prospect', ['filters' => ['query' => 'dupont']])));
        // Every word must be found, in any column
        $this->assertSame($this->expected('p1'), $this->ids($this->search('admin', 'prospect', ['filters' => ['query' => 'jean acme']])));
        $this->assertSame([], $this->ids($this->search('admin', 'prospect', ['filters' => ['query' => 'jean martin']])));
        $this->assertSame($this->expected('p5'), $this->ids($this->search('admin', 'prospect', ['filters' => ['processed' => true]])));
        $this->assertSame($this->expected('p1', 'p2', 'p3', 'p4'), $this->ids($this->search('admin', 'prospect', ['filters' => ['processed' => false]])));
        $this->assertSame($this->expected('p2'), $this->ids($this->search('admin', 'prospect', ['filters' => ['assigned_user_id' => $this->u['t1']]])));
        $this->assertSame($this->expected('p3'), $this->ids($this->search('admin', 'prospect', ['filters' => ['group_id' => $this->group]])));
        $this->assertSame($this->expected('p1'), $this->ids($this->search('admin', 'prospect', ['filters' => ['created_by_user_id' => $this->u['t1']]])));
        $this->assertSame($this->expected('p2'), $this->ids($this->search('admin', 'prospect', ['filters' => ['city' => 'Paris']])));
        // A date without time covers the whole day
        $this->assertSame($this->expected('p3'), $this->ids($this->search('admin', 'prospect', ['filters' => ['created_after' => '2026-10-14', 'created_before' => '2026-10-14']])));
        $this->assertSame($this->expected('p4', 'p5'), $this->ids($this->search('admin', 'prospect', ['filters' => ['created_after' => '2026-10-15']])));
    }

    /**
     * The reference is the CRM itself: the same user, the same project, asked
     * through the real API of the interface (middlewares, policies and scopes
     * included). What the agent lists must be what the interface lists.
     */
    public function test_the_prospects_an_agent_lists_are_the_ones_the_interface_lists(): void
    {
        // A login is not what is tested here: no tracking, no external geolocation
        Event::fake([Authenticated::class]);
        $this->withoutMiddleware([UserTrackActivity::class]);

        $slug = DB::table('projects')->where('id', $this->pid)->value('slug');

        // How many each one sees: the reference itself must not be empty by accident
        foreach (['admin' => 5, 't1' => 3, 't2' => 0, 't3' => 5, 'bare' => 0] as $who => $expected) {
            $response = $this->actingAs(User::find($this->u[$who]), 'sanctum')->getJson("/api/project/$slug/prospect?count=100");
            $response->assertOk();

            $api = collect($response->json('data'))->pluck('id')->sort()->values()->all();

            $this->assertCount($expected, $api, "L'interface renvoie un nombre inattendu de prospects pour $who.");
            $this->assertSame($api, $this->ids($this->search($who, 'prospect', ['limit' => 50])), "L'agent et l'interface diffèrent pour $who.");
        }
    }

    public function test_a_percent_sign_in_a_search_is_not_a_wildcard(): void
    {
        $this->assertSame(0, $this->search('admin', 'prospect', ['filters' => ['query' => '%']])['total']);
    }

    public function test_personal_data_comes_back_only_when_asked_for(): void
    {
        $default = $this->search('admin', 'prospect', ['filters' => ['query' => 'dupont']])['rows'][0];
        $asked = $this->search('admin', 'prospect', ['filters' => ['query' => 'dupont'], 'fields' => ['email', 'city']])['rows'][0];

        $this->assertArrayNotHasKey('email', $default);
        $this->assertArrayNotHasKey('phone_number', $default);
        $this->assertSame('jean@acme.test', $asked['email']);
        $this->assertSame('Lyon', $asked['city']);
        // The identifier always comes back
        $this->assertArrayHasKey('id', $asked);
    }

    public function test_pages_do_not_overlap_and_the_size_is_bounded(): void
    {
        $first = $this->search('admin', 'prospect', ['limit' => 2, 'sort' => ['field' => 'id', 'direction' => 'asc']]);
        $second = $this->search('admin', 'prospect', ['limit' => 2, 'offset' => $first['next_offset'], 'sort' => ['field' => 'id', 'direction' => 'asc']]);
        $last = $this->search('admin', 'prospect', ['limit' => 2, 'offset' => 4, 'sort' => ['field' => 'id', 'direction' => 'asc']]);

        $this->assertSame(5, $first['total']);
        $this->assertTrue($first['has_more']);
        $this->assertSame(2, $first['next_offset']);
        $this->assertSame([], array_intersect($this->ids($first), $this->ids($second)));
        $this->assertSame(1, $last['returned']);
        $this->assertFalse($last['has_more']);
        $this->assertNull($last['next_offset']);

        config(['agents.max_rows' => 3]);
        $this->assertSame(3, $this->search('admin', 'prospect', ['limit' => 50])['returned']);
    }

    public function test_the_sort_is_honoured(): void
    {
        $names = array_column($this->search('admin', 'prospect', ['sort' => ['field' => 'last_name', 'direction' => 'asc']])['rows'], 'last_name');

        $this->assertSame(['Bernard', 'Dupont', 'Durand', 'Martin', 'Petit'], $names);
    }

    /**
     * @dataProvider refusals
     */
    public function test_what_does_not_exist_is_refused_with_a_message_that_lets_the_model_correct_itself(string $entity, array $params, string $message): void
    {
        try {
            $this->search('admin', $entity, $params);
            $this->fail('A refusal was expected.');
        } catch (AgentReadException $e) {
            $this->assertStringContainsString($message, $e->getMessage());
        }
    }

    public static function refusals(): array
    {
        return [
            'unknown entity' => ['password', [], 'Entité inconnue'],
            'unknown filter' => ['prospect', ['filters' => ['sql' => 'x']], 'Filtre inconnu'],
            'a filter of another entity' => ['prospect', ['filters' => ['direction' => 'incoming']], 'Filtre inconnu'],
            'unknown field' => ['prospect', ['fields' => ['password']], 'Champ inconnu'],
            'unknown sort' => ['prospect', ['sort' => ['field' => 'email']], 'Tri impossible'],
            'bad direction' => ['prospect', ['sort' => ['field' => 'id', 'direction' => 'sideways']], 'asc ou desc'],
            'bad type' => ['prospect', ['filters' => ['processed' => 'yes']], 'true ou false'],
            'bad enum' => ['call', ['filters' => ['direction' => 'sideways']], 'outgoing, incoming'],
            'bad date' => ['prospect', ['filters' => ['created_after' => 'not a date']], 'Date invalide'],
        ];
    }

    // Users

    public function test_an_administrator_sees_the_team_and_a_telepro_sees_only_who_they_may_assign(): void
    {
        $admin = array_column($this->search('admin', 'user')['rows'], 'id');
        $t1 = array_column($this->search('t1', 'user')['rows'], 'id');

        sort($admin);
        sort($t1);

        $this->assertSame([$this->u['admin'], $this->u['t1'], $this->u['t2'], $this->u['t3'], $this->u['bare']], $admin);
        $this->assertSame([$this->u['t1'], $this->u['t2']], $t1);
        $this->assertNotContains($this->u['stranger'], $admin);
    }

    public function test_users_come_back_with_their_roles_and_without_their_email_by_default(): void
    {
        $row = collect($this->search('admin', 'user', ['filters' => ['query' => 'Tele1']])['rows'])->first();

        $this->assertSame('Télépro', $row['roles']);
        $this->assertArrayNotHasKey('email', $row);
    }

    // Calls, SMS, appointments, orders

    public function test_calls_are_those_of_the_prospects_the_user_can_see_and_never_show_a_number(): void
    {
        $t1 = $this->search('t1', 'call', ['fields' => ['direction', 'status', 'duration_seconds']]);
        $admin = $this->search('admin', 'call');

        // 3 + 1 calls on p1 and p2 for t1 (p4 is not visible); 7 in the project for the admin (not the stranger's)
        $this->assertSame(4, $t1['total']);
        $this->assertSame(7, $admin['total']);
        $this->assertArrayNotHasKey('number', $t1['rows'][0]);
        $this->assertSame('outgoing', $t1['rows'][0]['direction']);
    }

    public function test_calls_can_be_filtered_and_their_duration_is_computed(): void
    {
        $result = $this->search('admin', 'call', ['filters' => ['user_id' => $this->u['t1'], 'started_after' => '2026-10-12', 'started_before' => '2026-10-18', 'status' => 'answered'], 'sort' => ['field' => 'started_at', 'direction' => 'asc']]);

        $this->assertSame(2, $result['total']);
        $this->assertSame([300, 600], array_column($result['rows'], 'duration_seconds'));
        $this->assertSame('Tele1', $result['rows'][0]['user']);
    }

    public function test_an_entity_needs_the_permission_the_interface_asks_for(): void
    {
        $this->expectException(AgentReadException::class);
        $this->expectExceptionMessage('prospectInteractionView');

        $this->search('bare', 'call');
    }

    public function test_sms_are_limited_to_visible_prospects_and_the_text_is_cut(): void
    {
        $t1 = $this->search('t1', 'sms', ['fields' => ['direction', 'message']]);
        $directions = array_column($t1['rows'], 'direction', 'id');

        $this->assertSame(2, $t1['total']);
        $this->assertSame(3, $this->search('admin', 'sms')['total']);
        $this->assertContains('received', $directions);

        DB::table('sms')->where('prospect_id', $this->p['p1'])->update(['message' => str_repeat('x', 1000)]);
        $long = $this->search('t1', 'sms', ['fields' => ['message'], 'filters' => ['prospect_id' => $this->p['p1']]]);
        $this->assertSame(300, mb_strlen($long['rows'][0]['message']));
    }

    public function test_appointments_follow_the_calendars_and_the_prospects_of_the_user_and_skip_deleted_ones(): void
    {
        $t1 = array_column($this->search('t1', 'appointment')['rows'], 'name');
        $admin = array_column($this->search('admin', 'appointment')['rows'], 'name');
        sort($t1);
        sort($admin);

        $this->assertSame(['RDV Dupont', 'Tâche de t1'], $t1);
        $this->assertSame(['RDV Bernard', 'RDV Dupont', 'Tâche de t1'], $admin);
        $this->assertSame('RDV Tel', $this->search('admin', 'appointment')['rows'][0]['calendar']);
    }

    public function test_orders_are_those_of_the_prospects_the_user_can_see(): void
    {
        $admin = $this->search('admin', 'order', ['fields' => ['number', 'status']]);
        $t1 = $this->search('t1', 'order', ['fields' => ['number']]);

        $this->assertSame(3, $admin['total']);
        $this->assertSame('Nouveau', $admin['rows'][0]['status']);
        // Like the interface (orders of the visible prospects): p1 and p2 are visible to t1, p4 is not
        $this->assertEqualsCanonicalizing([1, 3], array_column($t1['rows'], 'number'));
    }

    // Statistics

    protected function stats(string $who, string $method, ...$arguments): array
    {
        return $this->actAs($who, fn ($context) => app(StatsGateway::class)->$method($context, ...$arguments));
    }

    public function test_the_calls_per_user_match_the_database(): void
    {
        $result = $this->stats('admin', 'aggregate', ['source' => 'interactions', 'dimension' => 'user', 'period' => self::A]);
        $byName = array_column($result['rows'], 'value', 'label');
        $direct = DB::table('interactions as t')->join('prospects as p', 'p.id', '=', 't.prospect_id')
            ->where('p.project_id', $this->pid)->whereBetween('t.started_at', ['2026-10-12 00:00:00', '2026-10-18 23:59:59'])->count();

        $this->assertSame(3.0, (float) $byName['Tele1']);
        $this->assertSame(1.0, (float) $byName['Tele2']);
        $this->assertSame((float) $direct, (float) $result['total']);
        $this->assertSame(['from' => '2026-10-12', 'to' => '2026-10-18'], $result['period']);
        // The stranger's call is not counted
        $this->assertArrayNotHasKey('Stranger', $byName);
    }

    public function test_each_day_comes_with_its_weekday_computed_by_the_server(): void
    {
        $rows = collect($this->stats('admin', 'aggregate', ['source' => 'interactions', 'dimension' => 'day', 'period' => self::A])['rows'])->keyBy('label');

        // 2026-10-13 is a Tuesday, 2026-10-15 a Thursday
        $this->assertSame('mardi', $rows['2026-10-13']['weekday']);
        $this->assertSame('jeudi', $rows['2026-10-15']['weekday']);
    }

    public function test_a_total_without_dimension_and_a_filter_on_a_dimension(): void
    {
        $total = $this->stats('admin', 'aggregate', ['source' => 'interactions', 'period' => self::A]);
        $answered = $this->stats('admin', 'aggregate', ['source' => 'interactions', 'period' => self::A, 'filters' => [['field' => 'status', 'value' => 'answered']]]);

        $this->assertSame(4.0, (float) $total['total']);
        $this->assertSame(3.0, (float) $answered['total']);
    }

    public function test_a_telepro_only_gets_the_figures_of_the_users_they_may_see(): void
    {
        $t1 = $this->stats('t1', 'aggregate', ['source' => 'interactions', 'dimension' => 'user', 'period' => self::A]);
        $asked = $this->stats('t1', 'aggregate', ['source' => 'interactions', 'dimension' => 'user', 'period' => self::A, 'user_ids' => [$this->u['t3'], $this->u['t1']]]);
        $forbidden = $this->stats('t1', 'aggregate', ['source' => 'interactions', 'dimension' => 'user', 'period' => self::A, 'user_ids' => [$this->u['t3']]]);
        $stranger = $this->stats('admin', 'aggregate', ['source' => 'interactions', 'dimension' => 'user', 'period' => self::A, 'user_ids' => [$this->u['stranger']]]);

        $this->assertEqualsCanonicalizing(['Tele1', 'Tele2'], array_column($t1['rows'], 'label'));
        // t3 is not visible to t1: only t1 remains
        $this->assertSame(['Tele1'], array_column($asked['rows'], 'label'));
        // An empty answer, never "everyone" (the engine reads an empty list as no restriction)
        $this->assertSame([], $forbidden['rows']);
        $this->assertSame(0, $forbidden['total']);
        // Someone of another project is not in this project's figures
        $this->assertSame(0.0, (float) array_sum(array_column($stranger['rows'], 'value')));
    }

    public function test_the_team_table_holds_the_figures_and_nothing_else(): void
    {
        $team = $this->stats('admin', 'team', self::A);
        $rows = collect($team['rows'])->keyBy('name');

        $this->assertCount(5, $team['rows']);
        $this->assertSame(3.0, $rows['Tele1']['calls_count']);
        $this->assertSame(15.5, $rows['Tele1']['call_duration']);
        $this->assertSame(67.0, $rows['Tele1']['performance']);
        $this->assertSame(100.0, $rows['Tele2']['performance']);
        $this->assertSame(0.0, $rows['Superviseur']['calls_count']);
        // No e-mail, no link to a recording
        foreach ($team['rows'] as $row) {
            $this->assertEqualsCanonicalizing(['user_id', 'name', 'role', 'connection_status', 'calls_count', 'call_duration', 'sms_count', 'orders_count', 'performance', 'prospects_count'], array_keys($row));
        }

        $this->assertCount(2, $this->stats('t1', 'team', self::A)['rows']);
    }

    public function test_the_comparison_is_computed_by_the_server(): void
    {
        $result = $this->stats('admin', 'compare', self::A, self::B, ['calls_count', 'performance']);
        $people = collect($result['people'])->keyBy('name');

        $this->assertSame(['from' => '2026-10-12', 'to' => '2026-10-18'], $result['period']);
        $this->assertSame(['from' => '2026-10-05', 'to' => '2026-10-11'], $result['compared_to']);

        // Tele1: 3 calls against 1, 67 % answered against 100 %
        $this->assertSame(['current' => 3.0, 'previous' => 1.0, 'delta' => 2.0, 'delta_percent' => 200.0, 'trend' => 'improved'], $people['Tele1']['metrics']['calls_count']);
        $this->assertSame(-33.0, $people['Tele1']['metrics']['performance']['delta']);
        $this->assertSame('declined', $people['Tele1']['metrics']['performance']['trend']);
        // Tele2: 1 call against 2
        $this->assertSame(-1.0, $people['Tele2']['metrics']['calls_count']['delta']);
        $this->assertSame(-50.0, $people['Tele2']['metrics']['calls_count']['delta_percent']);
        // Nothing to compare for somebody who never called: stable, no percentage
        $this->assertSame('stable', $people['Superviseur']['metrics']['calls_count']['trend']);
        $this->assertNull($people['Superviseur']['metrics']['calls_count']['delta_percent']);

        $summary = $result['summary']['calls_count'];
        $this->assertSame(1, $summary['improved']);
        $this->assertSame(1, $summary['declined']);
        $this->assertSame(3, $summary['stable']);
        $this->assertSame(4.0, $summary['total_current']);
        $this->assertSame(3.0, $summary['total_previous']);
        $this->assertSame('Tele1', $summary['biggest_improvement']['name']);
        $this->assertSame('Tele2', $summary['biggest_decline']['name']);
    }

    /**
     * "Compare my téléconseillers over the last two weeks": the real loop, the
     * real tools, a scripted model. The administrator gets the whole team, the
     * telepro only the colleagues they may see.
     */
    public function test_a_comparison_scenario_runs_end_to_end_with_the_real_tools(): void
    {
        Queue::fake();

        foreach (['admin' => 5, 't1' => 2] as $who => $people) {
            $agent = Agent::create([
                'user_id' => $this->u[$who],
                'name' => 'Lyra',
                'model' => 'claude-opus-5-5',
                'effort' => 'medium',
                'autonomy' => Agent::READ_ONLY,
                'status' => Agent::ACTIVE,
            ]);
            DB::table('agent_skills')->insert(['agent_id' => $agent->id, 'skill_key' => 'statistics_analysis']);

            $gateway = new ScriptedGateway([
                ScriptedGateway::toolUse('compare_periods', ['period' => self::A, 'compare_to' => self::B, 'metrics' => ['calls_count', 'performance']]),
                ScriptedGateway::text('Rapport prêt.'),
            ]);
            app()->instance(ClaudeGatewayFactory::class, new ScriptedGatewayFactory($gateway));

            $owner = User::find($this->u[$who]);
            $project = Project::withoutGlobalScopes()->find($this->pid);
            $run = app(AgentConversationService::class)->ask($agent, $owner, $project, 'Compare mes téléconseillers sur les deux dernières semaines.');
            app(AgentRunner::class)->run($run->id);
            $run = $run->fresh();

            $this->assertSame('completed', $run->status, "$who : {$run->error}");

            // The model was given the tools of its skill, in a fixed order
            $this->assertSame(['compare_periods', 'crm_aggregate', 'crm_describe', 'team_performance'], array_column($gateway->requests[0]['tools'], 'name'));
            $this->assertStringContainsString('compare_periods', $gateway->requests[0]['system'][0]['text']);

            // What the tool gave back to the model
            $message = AgentMessage::where('conversation_id', $run->conversation_id)->where('role', 'user')->orderByDesc('id')->first();
            $this->assertArrayNotHasKey('is_error', $message->content[0], "$who : " . $message->content[0]['content']);
            preg_match('#<crm_data untrusted="true">(.*)</crm_data>#s', $message->content[0]['content'], $matches);
            $data = json_decode($matches[1], true);
            $names = array_column($data['people'], 'name');

            $this->assertCount($people, $data['people'], "$who : " . implode(', ', $names));
            $this->assertContains('Tele1', $names);
            $this->assertEquals(2.0, collect($data['people'])->firstWhere('name', 'Tele1')['metrics']['calls_count']['delta']);

            $step = AgentRunStep::where('run_id', $run->id)->where('type', 'tool_call')->first();
            $this->assertSame('compare_periods', $step->tool_key);
            $this->assertSame('allow', $step->permission_decision);
            $this->assertSame($people, $step->rows);
        }
    }

    public function test_a_comparison_of_a_period_with_itself_is_refused(): void
    {
        $this->expectException(AgentReadException::class);

        $this->stats('admin', 'compare', self::A, self::A);
    }

    /**
     * @dataProvider statisticsRefusals
     */
    public function test_a_statistic_that_does_not_exist_is_refused_instead_of_answering_something_else(array $params, string $message): void
    {
        try {
            $this->stats('admin', 'aggregate', $params);
            $this->fail('A refusal was expected.');
        } catch (AgentReadException $e) {
            $this->assertStringContainsString($message, $e->getMessage());
        }
    }

    public static function statisticsRefusals(): array
    {
        return [
            'unknown source' => [['source' => 'passwords'], 'Source inconnue'],
            'unknown measure' => [['source' => 'interactions', 'measure' => 'revenue'], 'Mesure inconnue'],
            'impossible aggregation' => [['source' => 'interactions', 'agg' => 'sum'], 'Agrégation impossible'],
            'unknown dimension' => [['source' => 'interactions', 'dimension' => 'salary'], 'Dimension inconnue'],
            'unknown period' => [['source' => 'interactions', 'period' => ['preset' => 'next_century']], 'Période inconnue'],
            'custom period without dates' => [['source' => 'interactions', 'period' => ['preset' => 'custom']], 'from et to'],
            'inverted period' => [['source' => 'interactions', 'period' => ['preset' => 'custom', 'from' => '2026-10-18', 'to' => '2026-10-12']], 'avant son début'],
            'period over a year' => [['source' => 'interactions', 'period' => ['preset' => 'custom', 'from' => '2024-01-01', 'to' => '2026-01-01']], '366 jours'],
            'unknown filter field' => [['source' => 'interactions', 'filters' => [['field' => 'salary', 'value' => '1']]], 'Filtre impossible'],
        ];
    }
}
