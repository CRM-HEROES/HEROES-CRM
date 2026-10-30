<?php

namespace Tests\Feature;

use App\Http\Controllers\API\Project\Prospect\LabelController;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\User;
use App\Utils\Stat\Builder\DashboardEngine;
use Carbon\Carbon;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\TestCase;

class DashboardFollowupCountsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // These tests never migrate or write to the configured project database.
        config([
            'database.default' => 'dashboard_followup_test',
            'database.connections.dashboard_followup_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
            'cache.default' => 'array',
        ]);
        Carbon::setTestNow('2026-10-30 14:00:00');

        $tables = [
            'projects' => ['slug'],
            'users' => ['name', 'last_name', 'email', 'last_activity'],
            'user_project' => ['project_id', 'user_id', 'last_activity', 'prospects_count', 'relevance_event', 'relevance_prospect', 'relevance_order_action', 'relevance_message'],
            'roles' => ['project_id', 'name'],
            'model_has_roles' => ['project_id', 'model_id', 'model_type', 'role_id'],
            'groups' => ['project_id', 'name'],
            'user_group' => ['group_id', 'user_id'],
            'prospects' => ['project_id', 'creator_id', 'processed_at'],
            'categories' => ['project_id', 'for', 'name'],
            'labels' => ['category_id', 'name', 'creator_id'],
            'prospect_label' => ['prospect_id', 'label_id', 'creator_id'],
            'revisions' => ['revisionable_id', 'revisionable_type', 'key', 'old_value', 'new_value', 'user_id'],
            // Existing call metrics use MySQL's unquoted SECOND unit. The empty
            // fixture supplies that identifier so SQLite can parse the query.
            'interactions' => ['prospect_id', 'creator_id', 'source', 'status', 'path', 'started_at', 'ended_at', 'SECOND'],
            'sms' => ['prospect_id', 'creator_id', 'sent_at'],
            'orders' => ['prospect_id', 'creator_id'],
        ];
        foreach ($tables as $name => $columns) {
            DB::connection()->getSchemaBuilder()->create($name, function (Blueprint $table) use ($columns) {
                $table->id();
                foreach ($columns as $column) {
                    $table->text($column)->nullable();
                }
                $table->timestamps();
                $table->softDeletes();
            });
        }
        DB::connection()->getPdo()->sqliteCreateFunction('TIMESTAMPDIFF', static function ($unit, $from, $to) {
            return $from && $to ? strtotime($to) - strtotime($from) : null;
        }, 3);

        DB::table('projects')->insert([
            ['id' => 1, 'slug' => 'followup-stats'], ['id' => 2, 'slug' => 'other-project'],
        ]);
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Aria', 'last_name' => '', 'email' => 'aria@example.test'],
            ['id' => 2, 'name' => 'Admin', 'last_name' => '', 'email' => 'admin@example.test'],
            ['id' => 3, 'name' => 'No activity', 'last_name' => '', 'email' => 'zero@example.test'],
            ['id' => 4, 'name' => 'Other project', 'last_name' => '', 'email' => 'other@example.test'],
        ]);
        DB::table('user_project')->insert([
            ['project_id' => 1, 'user_id' => 1, 'prospects_count' => 25],
            ['project_id' => 1, 'user_id' => 2, 'prospects_count' => 50],
            ['project_id' => 1, 'user_id' => 3, 'prospects_count' => 0],
            ['project_id' => 2, 'user_id' => 4, 'prospects_count' => 1],
        ]);
        DB::table('prospects')->insert([
            ['id' => 1, 'project_id' => 1, 'creator_id' => 2, 'deleted_at' => null],
            ['id' => 2, 'project_id' => 1, 'creator_id' => 2, 'deleted_at' => null],
            ['id' => 3, 'project_id' => 2, 'creator_id' => 1, 'deleted_at' => null],
            ['id' => 4, 'project_id' => 1, 'creator_id' => 1, 'deleted_at' => '2026-10-15 12:00:00'],
            ['id' => 5, 'project_id' => 1, 'creator_id' => 2, 'deleted_at' => null],
            ['id' => 6, 'project_id' => 1, 'creator_id' => 2, 'deleted_at' => null],
        ]);
        DB::table('categories')->insert([
            ['id' => 1, 'project_id' => 1, 'for' => 'prospect', 'name' => 'Suivi', 'deleted_at' => null],
            ['id' => 2, 'project_id' => 2, 'for' => 'prospect', 'name' => 'Other project', 'deleted_at' => null],
            ['id' => 3, 'project_id' => 1, 'for' => 'order', 'name' => 'Order status', 'deleted_at' => null],
            ['id' => 4, 'project_id' => 1, 'for' => 'prospect', 'name' => 'Deleted category', 'deleted_at' => '2026-10-01 00:00:00'],
        ]);
        DB::table('labels')->insert([
            ['id' => 11, 'category_id' => 1, 'name' => 'NRP', 'creator_id' => 2, 'deleted_at' => null],
            ['id' => 12, 'category_id' => 1, 'name' => 'NRP 2', 'creator_id' => 2, 'deleted_at' => null],
            ['id' => 13, 'category_id' => 1, 'name' => 'SAV', 'creator_id' => 2, 'deleted_at' => null],
            ['id' => 21, 'category_id' => 2, 'name' => 'NRP', 'creator_id' => 2, 'deleted_at' => null],
            ['id' => 22, 'category_id' => 3, 'name' => 'Order-only', 'creator_id' => 2, 'deleted_at' => null],
            ['id' => 23, 'category_id' => 4, 'name' => 'Removed category label', 'creator_id' => 2, 'deleted_at' => null],
            ['id' => 24, 'category_id' => 1, 'name' => 'Deleted label', 'creator_id' => 2, 'deleted_at' => '2026-10-01 00:00:00'],
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        DB::purge('dashboard_followup_test');
        parent::tearDown();
    }

    public function test_counts_applications_by_actor_including_reattachments_and_excluding_removals_and_unchanged_statuses(): void
    {
        $this->revision('', 'NRP', ['created_at' => '2026-10-01 12:00:00']);
        $this->revision('NRP', '', ['created_at' => '2026-10-02 12:00:00']);
        $this->revision('', 'NRP', ['created_at' => '2026-10-03 12:00:00']);
        $this->revision('NRP', 'NRP, SAV');
        $this->revision('NRP, SAV', 'NRP, SAV');
        $this->revision('', 'NRP 2');
        $this->revision('NRP, SAV', 'SAV');
        $this->revision('', 'NRP', ['key' => 'first_name']);
        $this->revision('', 'NRP', ['user_id' => 2]);

        // No active assignments remain: this is an activity count, not a stock count.
        $this->assertSame(0, DB::table('prospect_label')->count());
        $result = $this->query([11, 12, 13]);
        $rows = collect($result['rows'])->keyBy('id');

        $this->assertSame(2, $rows[1]['followup_11']);
        $this->assertSame(1, $rows[1]['followup_12']);
        $this->assertSame(1, $rows[1]['followup_13']);
        $this->assertSame(1, $rows[2]['followup_11']);
        $this->assertSame(0, $rows[2]['followup_13']);
        $this->assertSame(0, $rows[3]['followup_11']);
        $this->assertSame(3, $result['total']);
        $this->assertEqualsCanonicalizing(['name', 'followup_11', 'followup_12', 'followup_13'], array_column($result['columns'], 'field'));
        $this->assertSame('number', collect($result['columns'])->firstWhere('field', 'followup_11')['type']);
    }

    public function test_preserved_baselines_and_live_assignments_count_once_with_original_author_and_date(): void
    {
        $this->assignment(1, 11, 1, '2026-10-10 12:00:00');
        $this->revision('', 'NRP', ['created_at' => '2026-10-10 12:00:00']);

        // Same category and second but another status must not hide an import.
        $this->assignment(2, 11, 2, '2026-10-12 12:00:00');
        $this->revision('', 'SAV', ['revisionable_id' => 2, 'user_id' => 2, 'created_at' => '2026-10-12 12:00:00']);

        $this->assignment(5, 11, 1, '2026-10-13 12:00:00');
        $this->revision(null, 'NRP', ['revisionable_id' => 5, 'created_at' => '2026-10-13 12:00:00']);

        // Another user's revision is a distinct action from the imported assignment.
        $this->assignment(6, 11, 1, '2026-10-14 12:00:00');
        $this->revision('', 'NRP', ['revisionable_id' => 6, 'user_id' => 2, 'created_at' => '2026-10-14 12:00:00']);

        $rows = collect($this->query([11, 13])['rows'])->keyBy('id');
        $this->assertSame(3, $rows[1]['followup_11']);
        $this->assertSame(2, $rows[2]['followup_11']);
        $this->assertSame(1, $rows[2]['followup_13']);
        $this->assertSame(0, $rows[3]['followup_11']);
    }

    public function test_scope_is_the_selected_project_prospect_category_morph_type_and_action_period(): void
    {
        $this->revision('', 'NRP');
        $this->revision('', 'NRP', ['revisionable_id' => 3]);
        $this->revision('', 'NRP', ['revisionable_id' => 4]);
        $this->revision('', 'NRP', ['key' => 'category->2']);
        $this->revision('', 'NRP', ['revisionable_type' => User::class]);
        $this->revision('', 'NRP', ['user_id' => 4]);
        $this->revision('', 'NRP', ['created_at' => '2026-09-30 23:59:59']);
        $this->revision('', 'NRP', ['created_at' => '2026-11-01 00:00:00']);
        $this->assignment(3, 11, 1, '2026-10-15 12:00:00');
        $this->assignment(4, 11, 1, '2026-10-15 12:00:00');
        $this->assignment(2, 11, 1, '2026-09-30 23:59:59');

        $rows = collect($this->query([11])['rows'])->keyBy('id');
        $this->assertSame(1, $rows[1]['followup_11']);
        $this->assertSame(0, $rows[2]['followup_11']);
        $this->assertArrayNotHasKey(4, $rows->all());
    }

    public function test_period_bounds_global_user_filter_and_global_period_apply_to_followup_actions(): void
    {
        $this->revision('', 'NRP', ['created_at' => '2026-10-01 00:00:00']);
        $this->revision('', 'NRP', ['created_at' => '2026-10-31 23:59:59']);
        $this->revision('', 'NRP', ['user_id' => 2, 'created_at' => '2026-10-20 10:00:00']);
        $this->revision('', 'NRP', ['user_id' => 2, 'created_at' => '2026-10-21 10:00:00']);

        $all = collect($this->query([11])['rows'])->keyBy('id');
        $this->assertSame(2, $all[1]['followup_11']);

        $filtered = $this->query([11], [], [
            'user' => [2],
            'period' => ['preset' => 'custom', 'from' => '2026-10-20', 'to' => '2026-10-20'],
        ]);
        $this->assertSame(1, $filtered['total']);
        $this->assertSame(2, (int) $filtered['rows'][0]['id']);
        $this->assertSame(1, $filtered['rows'][0]['followup_11']);
        $this->assertSame(['2026-10-20', '2026-10-20'], $filtered['period']);

        $local = $this->query([11], ['globalFilters' => []], ['user' => [2], 'period' => ['preset' => 'today']]);
        $this->assertSame(3, $local['total']);
        $this->assertSame(2, collect($local['rows'])->firstWhere('id', 1)['followup_11']);
    }

    public function test_multiple_followup_columns_support_numeric_sort_pagination_and_custom_heading(): void
    {
        $this->revision('', 'NRP');
        for ($i = 0; $i < 3; $i++) {
            $this->revision('', 'NRP', ['user_id' => 2]);
        }
        $this->revision('', 'SAV');

        $result = $this->query([11, 13], [
            'columns' => [['field' => 'name'], ['field' => 'followup_11', 'label' => 'NRP effectués'], ['field' => 'followup_13']],
            'sorting' => ['field' => 'followup_11', 'direction' => 'desc'],
            'pagination' => true,
            'perPage' => 1,
        ]);
        $this->assertSame(3, $result['total']);
        $this->assertSame(2, (int) $result['rows'][0]['id']);
        $this->assertSame(3, $result['rows'][0]['followup_11']);
        $this->assertSame(0, $result['rows'][0]['followup_13']);
        $this->assertSame('NRP effectués', collect($result['columns'])->firstWhere('field', 'followup_11')['label']);

        $next = $this->query([11, 13], ['pagination' => true, 'perPage' => 1], [], [
            'page' => 2, 'sort' => ['field' => 'followup_11', 'direction' => 'desc'],
        ]);
        $this->assertSame(2, $next['page']);
        $this->assertSame(1, (int) $next['rows'][0]['id']);
        $this->assertSame(1, $next['rows'][0]['followup_11']);
    }

    public function test_complete_names_with_commas_are_counted_without_counting_fragments_or_retained_statuses(): void
    {
        DB::table('labels')->insert(['id' => 14, 'category_id' => 1, 'name' => 'RDV, à confirmer', 'creator_id' => 2]);
        $this->revision('NRP', 'NRP, RDV, à confirmer');
        $this->revision('NRP, RDV, à confirmer', 'NRP, RDV, à confirmer, SAV');
        $this->revision('NRP, RDV, à confirmer, SAV', 'NRP, SAV');
        $rows = collect($this->query([11, 14])['rows'])->keyBy('id');

        $this->assertSame(0, $rows[1]['followup_11']);
        $this->assertSame(1, $rows[1]['followup_14']);
    }

    public function test_catalogue_offers_project_prospect_followups_and_a_numeric_users_column(): void
    {
        $meta = $this->engine()->meta();
        $source = collect($meta['sources'])->firstWhere('key', 'users');
        $field = collect($source['fields'])->firstWhere('key', 'followup');
        $this->assertSame('Suivi', $field['label']);
        $this->assertSame('number', $field['type']);

        $options = collect($meta['options']['followup']);
        $this->assertEqualsCanonicalizing([11, 12, 13], $options->pluck('value')->map(fn ($id) => (int) $id)->all());
        $nrp = $options->first(fn ($option) => (int) $option['value'] === 11);
        $this->assertSame('NRP', $nrp['label']);
        $this->assertSame('Suivi', $nrp['category']);
    }

    public function test_pending_unknown_cross_project_and_non_prospect_followup_columns_are_rejected(): void
    {
        foreach (['followup', 'followup_9999', 'followup_21', 'followup_22', 'followup_23', 'followup_24'] as $field) {
            try {
                $this->query([], ['columns' => [['field' => 'name'], ['field' => $field]]]);
                $this->fail('Expected an invalid followup column to be rejected: ' . $field);
            } catch (HttpExceptionInterface $exception) {
                $this->assertSame(422, $exception->getStatusCode(), $field);
            }
        }
    }

    public function test_bulk_removal_keeps_original_activity_without_duplicating_a_recorded_assignment(): void
    {
        $this->assignment(1, 11, 1, '2026-10-10 09:00:00');
        $this->revision('', 'NRP', ['created_at' => '2026-10-10 09:00:00']);
        $this->assignment(2, 11, 1, '2026-10-11 09:00:00');
        $this->assignment(5, 11, 1, '2026-10-09 09:00:00');
        DB::table('prospect_label')->where('prospect_id', 5)->update(['deleted_at' => '2026-10-12 09:00:00']);
        $this->assignment(1, 13, 1, '2026-10-10 09:00:00');
        $before = collect($this->query([11, 13])['rows'])->keyBy('id');

        $this->bulk('detach', [1, 2, 5], [11], 2, '2026-10-20 12:00:00');

        $after = collect($this->query([11, 13])['rows'])->keyBy('id');
        $this->assertSame(3, $before[1]['followup_11']);
        $this->assertSame($before[1]['followup_11'], $after[1]['followup_11']);
        $this->assertSame(0, $after[2]['followup_11']);
        $this->assertSame(1, $after[1]['followup_13']);
        $this->assertSame([13], DB::table('prospect_label')->pluck('label_id')->map(fn ($id) => (int) $id)->all());
        $this->assertSame(3, DB::table('revisions')->count());
        $preserved = DB::table('revisions')->where('revisionable_id', 2)->first();
        $this->assertNull($preserved->old_value);
        $this->assertSame('NRP', $preserved->new_value);
        $this->assertSame(1, (int) $preserved->user_id);
        $this->assertSame('2026-10-11 09:00:00', $preserved->created_at);
        $this->assertSame('2026-10-09 09:00:00', DB::table('revisions')->where('revisionable_id', 5)->value('created_at'));

        $this->bulk('detach', [1, 2, 5], [11], 2, '2026-10-21 12:00:00');
        $this->assertSame(3, DB::table('revisions')->count());
        $this->assertSame(3, collect($this->query([11])['rows'])->firstWhere('id', 1)['followup_11']);
    }

    public function test_repeated_bulk_replacements_preserve_each_original_action_and_count_the_new_actor(): void
    {
        $this->assignment(1, 11, 1, '2026-10-10 09:00:00');
        $this->assignment(2, 11, 1, '2026-10-11 09:00:00');

        $this->bulk('attach', [1, 2], [11], 2, '2026-10-20 12:00:00');
        $first = collect($this->query([11])['rows'])->keyBy('id');
        $this->assertSame(2, $first[1]['followup_11']);
        $this->assertSame(2, $first[2]['followup_11']);
        $this->assertSame(4, DB::table('revisions')->count());

        $this->bulk('attach', [1, 2], [11], 2, '2026-10-21 12:00:00');
        $second = collect($this->query([11])['rows'])->keyBy('id');
        $this->assertSame(2, $second[1]['followup_11']);
        $this->assertSame(4, $second[2]['followup_11']);
        $this->assertSame(6, DB::table('revisions')->count());

        $this->bulk('detach', [1, 2], [11], 1, '2026-10-22 12:00:00');
        $this->bulk('detach', [1, 2], [11], 1, '2026-10-23 12:00:00');
        $final = collect($this->query([11])['rows'])->keyBy('id');
        $this->assertSame(2, $final[1]['followup_11']);
        $this->assertSame(4, $final[2]['followup_11']);
        $this->assertSame(6, DB::table('revisions')->count());
        $this->assertSame(0, DB::table('prospect_label')->count());
        $this->assertEqualsCanonicalizing(
            ['2026-10-10 09:00:00', '2026-10-11 09:00:00'],
            DB::table('revisions')->where('user_id', 1)->pluck('created_at')->all()
        );
        $this->assertSame(2, DB::table('revisions')->where('user_id', 2)->where('created_at', '2026-10-20 12:00:00')->count());
        $this->assertSame(2, DB::table('revisions')->where('user_id', 2)->where('created_at', '2026-10-21 12:00:00')->count());
    }

    public function test_repeated_bulk_additions_in_the_same_second_are_distinct_actions(): void
    {
        $this->bulk('attach', [1], [11], 1, '2026-10-20 12:00:00');
        $this->bulk('attach', [1], [11], 1, '2026-10-20 12:00:00');

        $rows = collect($this->query([11])['rows'])->keyBy('id');
        $this->assertSame(2, $rows[1]['followup_11']);
        $this->assertSame(2, DB::table('revisions')->count());
        $this->assertSame(1, DB::table('prospect_label')->count());

        $this->bulk('detach', [1], [11], 2, '2026-10-21 12:00:00');
        $this->assertSame(2, DB::table('revisions')->count());
        $this->assertSame(2, collect($this->query([11])['rows'])->firstWhere('id', 1)['followup_11']);
    }

    private function bulk(string $action, array $prospects, array $labels, int $actor, string $time): void
    {
        Event::fake([Authenticated::class]);
        $this->actingAs((new User())->setRawAttributes(['id' => $actor, 'role' => 'super_admin']));
        Carbon::setTestNow($time);
        $request = Request::create('/', 'POST', compact('action', 'prospects', 'labels'));
        (new LabelController())->bulk($request, Project::findOrFail(1));
    }

    private function revision(?string $old, ?string $new, array $attributes = []): int
    {
        return DB::table('revisions')->insertGetId(array_replace([
            'revisionable_id' => 1,
            'revisionable_type' => (new Prospect())->getMorphClass(),
            'key' => 'category->1',
            'old_value' => $old,
            'new_value' => $new,
            'user_id' => 1,
            'created_at' => '2026-10-15 12:00:00',
            'updated_at' => '2026-10-15 12:00:00',
        ], $attributes));
    }

    private function assignment(int $prospect, int $label, int $author, string $date): void
    {
        DB::table('prospect_label')->insert([
            'prospect_id' => $prospect, 'label_id' => $label, 'creator_id' => $author,
            'created_at' => $date, 'updated_at' => $date,
        ]);
    }

    private function engine(): DashboardEngine
    {
        return new DashboardEngine(Project::findOrFail(1));
    }

    private function query(array $labels, array $overrides = [], array $global = [], array $runtime = []): array
    {
        return $this->engine()->run(array_replace([
            'type' => 'table',
            'dataSource' => 'users',
            'columns' => array_merge([['field' => 'name']], array_map(fn ($id) => ['field' => 'followup_' . $id], $labels)),
            'period' => ['preset' => 'custom', 'from' => '2026-10-01', 'to' => '2026-10-31'],
            'pagination' => false,
        ], $overrides), $global, $runtime);
    }
}
