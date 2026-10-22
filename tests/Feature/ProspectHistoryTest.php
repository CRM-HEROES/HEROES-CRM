<?php

namespace Tests\Feature;

use App\Http\Controllers\API\Project\ProspectController;
use App\Models\Message;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\User;
use App\Services\ProspectHistory;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class ProspectHistoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake([Authenticated::class]);
        config([
            'database.default' => 'prospect_history_test',
            'database.connections.prospect_history_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);

        $tables = [
            'projects' => ['slug'],
            'prospects' => ['project_id'],
            'users' => ['name', 'role'],
            'fields' => ['project_id', 'for', 'slug', 'meta', 'name'],
            'categories' => ['project_id', 'for', 'name'],
            'calendars' => ['project_id', 'name', 'color', 'bgcolor'],
            'events' => ['prospect_id', 'calendar_id', 'user_id', 'creator_id', 'started_at', 'location', 'description'],
            'folders' => ['project_id', 'for', 'name'],
            'files' => ['prospect_id', 'folder_id', 'name', 'creator_id'],
            'groups' => ['name'],
            'prospect_group' => ['prospect_id', 'group_id', 'creator_id'],
            'labels' => ['name', 'category_id', 'color', 'bgcolor'],
            'prospect_label' => ['prospect_id', 'label_id', 'creator_id'],
            'links' => ['prospect_id', 'name', 'link', 'creator_id'],
            'threads' => ['project_id', 'name'],
            'messages' => ['prospect_id', 'thread_id', 'body', 'creator_id'],
            'orders' => ['prospect_id', 'name', 'creator_id'],
            'products' => ['name'],
            'order_product' => ['order_id', 'product_id', 'price', 'tax', 'including_tax', 'quantity', 'currency', 'meta'],
            'revisions' => ['revisionable_id', 'revisionable_type', 'key', 'old_value', 'new_value', 'user_id'],
            'sms' => ['prospect_id', 'source', 'message', 'creator_id'],
            'interactions' => ['prospect_id', 'source', 'creator_id'],
            'prospect_user' => ['prospect_id', 'user_id', 'creator_id'],
            'user_folder' => ['folder_id', 'user_id'],
            'user_file' => ['file_id', 'user_id'],
            'user_assignable_user' => ['user_id', 'assignable_user_id', 'project_id'],
            'user_settings' => ['project_id', 'user_id', 'key', 'value'],
            'project_settings' => ['project_id', 'key', 'value'],
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

        $user = Mockery::mock(User::class)->makePartial();
        $user->setRawAttributes(['id' => 1, 'name' => 'Actor', 'role' => 'super_admin']);
        $user->shouldReceive('can')->with('prospectEventView', Mockery::type(Project::class))->andReturn(true);
        $this->actingAs($user);

        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Actor', 'role' => 'super_admin', 'created_at' => '2020-01-01 00:00:00'],
            ['id' => 2, 'name' => 'Assigned user', 'role' => 'user', 'created_at' => '2020-01-01 00:00:00'],
        ]);
        DB::table('projects')->insert(['id' => 1, 'slug' => 'history-test']);
        DB::table('prospects')->insert([['id' => 1, 'project_id' => 1], ['id' => 2, 'project_id' => 1]]);
        DB::table('fields')->insert(['project_id' => 1, 'for' => 'prospect', 'slug' => 'first_name', 'meta' => 0, 'name' => 'Prénom']);
        DB::table('categories')->insert(['id' => 1, 'project_id' => 1, 'for' => 'prospect', 'name' => 'Statut']);
        DB::table('calendars')->insert(['id' => 1, 'project_id' => 1, 'name' => 'Rendez-vous']);
        DB::table('threads')->insert(['id' => 1, 'project_id' => 1, 'name' => 'Suivi prospect']);
        DB::table('folders')->insert(['id' => 1, 'project_id' => 1, 'for' => 'prospect', 'name' => 'Documents']);
        DB::table('user_folder')->insert(['folder_id' => 1, 'user_id' => 1]);
    }

    protected function tearDown(): void
    {
        DB::purge('prospect_history_test');
        parent::tearDown();
    }

    public function test_history_combines_every_type_uses_assignment_dates_and_keeps_all_revisions(): void
    {
        $this->seedHistory();
        $prospects = Prospect::query()->whereKey(1)->get();
        $messages = new Collection([(new Message())->forceFill(['id' => 999, 'body' => 'Existing table relation'])]);
        $prospects->first()->setRelation('messages', $messages);

        (new ProspectHistory())->append($prospects, $this->project());

        $history = collect($prospects->first()->history);
        $this->assertEqualsCanonicalizing(
            ['event', 'file', 'group', 'interaction', 'label', 'link', 'message', 'order', 'revision', 'sms', 'user'],
            $history->pluck('type')->unique()->all()
        );
        $this->assertCount(30, $history->where('type', 'revision'));
        $this->assertSame('sms', $history->first()['type']);
        $this->assertSame('Actor', $history->first()['creator']->name);
        $this->assertSame('Prénom', $history->firstWhere('type', 'revision')['field']);
        $this->assertSame('Actor', $history->firstWhere('type', 'revision')['user']->name);
        $this->assertStringStartsWith('2026-10-22', $history->firstWhere('type', 'user')['created_at']);
        $this->assertSame($messages, $prospects->first()->getRelation('messages'));
        $this->assertSame('Existing table relation', $messages->first()->body);
        $this->assertStringContainsString('/folder/1/file/1/thumbnail', $history->firstWhere('type', 'file')['thumbnail']);
        $this->assertSame('Service', $history->firstWhere('type', 'order')['name']);
    }

    public function test_history_queries_are_batched_and_serializing_does_not_fetch_relations(): void
    {
        $this->seedHistory();
        $one = Prospect::query()->whereKey(1)->get();
        $two = Prospect::query()->whereIn('id', [1, 2])->get();
        $project = $this->project();

        DB::enableQueryLog();
        (new ProspectHistory())->append($one, $project);
        $oneCount = count(DB::getQueryLog());
        DB::flushQueryLog();
        (new ProspectHistory())->append($two, $project);
        $twoCount = count(DB::getQueryLog());
        $this->assertSame($oneCount, $twoCount);
        $this->assertLessThan(20, $twoCount);
        $this->assertSame([], $two->last()->history);

        DB::flushQueryLog();
        $two->toJson();
        $this->assertCount(0, DB::getQueryLog());
        DB::disableQueryLog();
    }

    public function test_inaccessible_files_other_project_relations_and_unknown_revision_fields_are_excluded(): void
    {
        $this->seedHistory();
        DB::table('calendars')->insert(['id' => 2, 'project_id' => 2, 'name' => 'Other']);
        DB::table('threads')->insert(['id' => 2, 'project_id' => 2, 'name' => 'Other']);
        DB::table('events')->insert(['id' => 2, 'prospect_id' => 1, 'calendar_id' => 2, 'created_at' => '2026-10-23']);
        DB::table('messages')->insert(['id' => 2, 'prospect_id' => 1, 'thread_id' => 2, 'body' => 'Other project', 'created_at' => '2026-10-23']);
        DB::table('folders')->insert(['id' => 2, 'project_id' => 1, 'for' => 'prospect', 'name' => 'Private']);
        DB::table('files')->insert(['id' => 2, 'prospect_id' => 1, 'folder_id' => 2, 'name' => 'Private file', 'created_at' => '2026-10-23']);
        DB::table('revisions')->insert(['revisionable_id' => 1, 'revisionable_type' => Prospect::class, 'key' => 'unknown', 'created_at' => '2026-10-23']);
        $prospects = Prospect::query()->whereKey(1)->get();

        (new ProspectHistory())->append($prospects, $this->project());
        $history = collect($prospects->first()->history);
        foreach (['event', 'message', 'file'] as $type) {
            $this->assertCount(1, $history->where('type', $type));
        }
        $this->assertCount(30, $history->where('type', 'revision'));

        DB::table('user_file')->insert(['file_id' => 2, 'user_id' => 1]);
        (new ProspectHistory())->append($prospects, $this->project());
        $this->assertCount(2, collect($prospects->first()->history)->where('type', 'file'));
    }

    public function test_hidden_history_is_excluded_from_user_and_project_settings(): void
    {
        $settings = json_encode([
            ['key' => 'history', 'hidden' => true],
            ['key' => 'first_name', 'hidden' => true],
        ]);
        DB::table('user_settings')->insert(['project_id' => 1, 'user_id' => 1, 'key' => 'prospects-table', 'value' => $settings]);
        $controller = new class extends ProspectController {
            public function fieldKeys(Request $request, Project $project): array
            {
                return array_values($this->getFields($request, $project));
            }
        };
        $this->assertSame(['first_name'], $controller->fieldKeys(Request::create('/'), $this->project()));
        DB::table('user_settings')->delete();
        DB::table('project_settings')->insert(['project_id' => 1, 'key' => 'prospects-table', 'value' => $settings]);
        $this->assertSame(['first_name'], $controller->fieldKeys(Request::create('/'), $this->project()));
        $this->assertSame(['history'], $controller->fieldKeys(Request::create('/', 'GET', ['fields' => 'history']), $this->project()));
    }

    public function test_summary_returns_latest_and_count_without_mutating_existing_relations(): void
    {
        $this->seedHistory();
        $prospects = Prospect::query()->whereIn('id', [1, 2])->get();
        $messages = new Collection([(new Message())->forceFill(['id' => 999, 'body' => 'Existing table relation'])]);
        $prospects->first()->setRelation('messages', $messages);

        (new ProspectHistory())->appendSummary($prospects, $this->project());

        $this->assertSame(40, $prospects->first()->history_count);
        $this->assertSame('sms', $prospects->first()->history_latest['type']);
        $this->assertSame('Latest', $prospects->first()->history_latest['message']);
        $this->assertSame('Actor', $prospects->first()->history_latest['creator']->name);
        $this->assertSame($messages, $prospects->first()->getRelation('messages'));
        $this->assertArrayNotHasKey('history', $prospects->first()->getAttributes());
        $this->assertSame(0, $prospects->last()->history_count);
        $this->assertNull($prospects->last()->history_latest);

        DB::enableQueryLog();
        $prospects->toJson();
        $this->assertCount(0, DB::getQueryLog());
        DB::disableQueryLog();
    }

    public function test_summary_aggregates_in_sql_and_only_hydrates_latest_entries_in_batches(): void
    {
        $this->seedHistory();
        foreach (range(1, 200) as $index) {
            DB::table('sms')->insert([
                'prospect_id' => 1, 'message' => 'Older ' . $index, 'created_at' => '2026-10-21 10:00:00',
            ]);
        }
        DB::table('sms')->insert(['prospect_id' => 2, 'message' => 'Other prospect', 'created_at' => '2026-10-22 15:00:00']);
        $one = Prospect::query()->whereKey(1)->get();
        $two = Prospect::query()->whereIn('id', [1, 2])->get();
        $project = $this->project();
        $retrieved = [];
        foreach (['Event', 'File', 'Group', 'Interaction', 'Label', 'Link', 'Message', 'Order', 'Sms'] as $model) {
            Event::listen('eloquent.retrieved: App\\Models\\' . $model, function ($item) use (&$retrieved) {
                $retrieved[] = $item;
            });
        }
        Event::listen('eloquent.retrieved: Venturecraft\\Revisionable\\Revision', function ($item) use (&$retrieved) {
            $retrieved[] = $item;
        });

        DB::enableQueryLog();
        (new ProspectHistory())->appendSummary($one, $project);
        $oneCount = count(DB::getQueryLog());
        $this->assertCount(1, $retrieved);
        $this->assertSame(240, $one->first()->history_count);
        $this->assertStringContainsString('COUNT(*) OVER', DB::getQueryLog()[2]['query']);

        DB::flushQueryLog();
        $retrieved = [];
        (new ProspectHistory())->appendSummary($two, $project);
        $this->assertSame($oneCount, count(DB::getQueryLog()));
        $this->assertLessThan(10, $oneCount);
        $this->assertCount(2, $retrieved);
        $this->assertSame(1, $two->last()->history_count);
        DB::disableQueryLog();
    }

    /** @dataProvider latestHistoryTypes */
    public function test_summary_matches_full_latest_entry_for_every_type_and_equal_dates(string $table, string $type): void
    {
        $this->seedHistory();
        DB::table($table)->update(['created_at' => '2026-10-25 12:00:00']);
        $summaryProspects = Prospect::query()->whereKey(1)->get();
        $fullProspects = Prospect::query()->whereKey(1)->get();
        $service = new ProspectHistory();
        $service->appendSummary($summaryProspects, $this->project());
        $service->append($fullProspects, $this->project());

        $this->assertSame($type, $summaryProspects->first()->history_latest['type']);
        $this->assertEquals($fullProspects->first()->history[0], $summaryProspects->first()->history_latest);
        $this->assertSame(count($fullProspects->first()->history), $summaryProspects->first()->history_count);
        if ($type === 'revision') {
            $this->assertSame(30, $summaryProspects->first()->history_latest['id']);
        }
    }

    public static function latestHistoryTypes(): array
    {
        return [
            ['events', 'event'], ['files', 'file'], ['prospect_group', 'group'], ['interactions', 'interaction'],
            ['prospect_label', 'label'], ['links', 'link'], ['messages', 'message'], ['orders', 'order'],
            ['revisions', 'revision'], ['sms', 'sms'], ['prospect_user', 'user'],
        ];
    }

    public function test_summary_and_full_apply_same_visibility_project_morph_and_missing_date_filters(): void
    {
        $this->seedHistory();
        $user = Mockery::mock(User::class)->makePartial();
        $user->setRawAttributes(['id' => 1, 'name' => 'Actor', 'role' => 'super_admin']);
        $user->shouldReceive('can')->with('prospectEventView', Mockery::type(Project::class))->andReturn(false);
        $this->actingAs($user);
        DB::table('user_assignable_user')->insert(['user_id' => 1, 'assignable_user_id' => 2, 'project_id' => 1]);
        DB::table('events')->insert([
            ['id' => 2, 'prospect_id' => 1, 'calendar_id' => 1, 'creator_id' => 3, 'created_at' => '2026-10-25'],
            ['id' => 3, 'prospect_id' => 1, 'calendar_id' => 1, 'creator_id' => 2, 'created_at' => '2026-10-23'],
        ]);
        DB::table('folders')->insert([
            ['id' => 2, 'project_id' => 1, 'for' => 'prospect', 'name' => 'Private'],
            ['id' => 3, 'project_id' => 2, 'for' => 'prospect', 'name' => 'Other project'],
        ]);
        DB::table('files')->insert([
            ['id' => 2, 'prospect_id' => 1, 'folder_id' => 2, 'name' => 'Private', 'created_at' => '2026-10-25'],
            ['id' => 3, 'prospect_id' => 1, 'folder_id' => 3, 'name' => 'Other project', 'created_at' => '2026-10-25'],
        ]);
        DB::table('user_folder')->insert(['folder_id' => 3, 'user_id' => 1]);
        DB::table('threads')->insert(['id' => 2, 'project_id' => 2, 'name' => 'Other']);
        DB::table('messages')->insert(['prospect_id' => 1, 'thread_id' => 2, 'created_at' => '2026-10-25']);
        DB::table('revisions')->insert([
            ['revisionable_id' => 1, 'revisionable_type' => Prospect::class, 'key' => 'unknown', 'created_at' => '2026-10-25'],
            ['revisionable_id' => 1, 'revisionable_type' => User::class, 'key' => 'first_name', 'created_at' => '2026-10-25'],
        ]);
        DB::table('sms')->insert(['prospect_id' => 1, 'message' => 'Undated']);
        DB::table('messages')->insert(['prospect_id' => 1, 'thread_id' => 1, 'created_at' => '2026-10-25', 'deleted_at' => '2026-10-25']);
        $summary = Prospect::query()->whereKey(1)->get();
        $full = Prospect::query()->whereKey(1)->get();
        $service = new ProspectHistory();
        $service->appendSummary($summary, $this->project());
        $service->append($full, $this->project());

        $this->assertSame(count($full->first()->history), $summary->first()->history_count);
        $this->assertEquals($full->first()->history[0], $summary->first()->history_latest);
        $this->assertSame('event', $summary->first()->history_latest['type']);
        $this->assertSame(3, $summary->first()->history_latest['id']);
    }

    public function test_update_returns_refreshed_summary_only_when_requested(): void
    {
        $prospect = Mockery::mock(Prospect::class)->makePartial();
        $prospect->forceFill(['id' => 1, 'meta' => []]);
        $prospect->shouldReceive('update')->twice()->andReturn(true);
        $service = Mockery::mock(ProspectHistory::class);
        $service->shouldReceive('appendSummary')->once()->withArgs(function ($prospects, $project) use ($prospect) {
            $this->assertSame($prospect, $prospects->first());
            $prospect->history_latest = ['type' => 'revision', 'new_value' => 'Updated'];
            $prospect->history_count = 12;
            return $project->id === 1;
        });
        $this->app->instance(ProspectHistory::class, $service);

        $controller = new ProspectController();
        $response = $controller->update(Request::create('/', 'PUT', ['withHistory' => true]), $this->project(), $prospect);
        $this->assertSame(['type' => 'revision', 'new_value' => 'Updated'], $response['history_latest']);
        $this->assertSame(12, $response['history_count']);
        $this->assertArrayNotHasKey('history', $response);
        $response = $controller->update(Request::create('/', 'PUT'), $this->project(), $prospect);
        $this->assertArrayNotHasKey('history_latest', $response);
        $this->assertArrayNotHasKey('history_count', $response);
    }

    public function test_history_endpoint_loads_full_timeline(): void
    {
        $this->seedHistory();
        $response = (new ProspectController())->history($this->project(), Prospect::query()->findOrFail(1));

        $this->assertCount(40, $response);
        $this->assertSame('sms', $response[0]['type']);
    }

    public function test_history_endpoint_rejects_a_prospect_from_another_project(): void
    {
        $prospect = Prospect::query()->findOrFail(1);
        $prospect->project_id = 2;
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        (new ProspectController())->history($this->project(), $prospect);
    }

    protected function project(): Project
    {
        return Project::query()->findOrFail(1);
    }

    protected function seedHistory(): void
    {
        $common = ['prospect_id' => 1, 'creator_id' => 1, 'created_at' => '2026-10-21 12:00:00'];
        DB::table('events')->insert($common + ['id' => 1, 'calendar_id' => 1, 'user_id' => 2, 'started_at' => '2026-11-01']);
        DB::table('files')->insert($common + ['id' => 1, 'folder_id' => 1, 'name' => 'Document.pdf']);
        DB::table('groups')->insert(['id' => 1, 'name' => 'Sales']);
        DB::table('prospect_group')->insert($common + ['group_id' => 1]);
        DB::table('labels')->insert(['id' => 1, 'category_id' => 1, 'name' => 'New']);
        DB::table('prospect_label')->insert($common + ['label_id' => 1]);
        DB::table('links')->insert($common + ['name' => 'Website', 'link' => 'https://example.test']);
        DB::table('interactions')->insert($common + ['source' => 'cloudtalk']);
        DB::table('messages')->insert($common + ['id' => 1, 'thread_id' => 1, 'body' => 'Follow-up']);
        DB::table('orders')->insert($common + ['name' => 'Quote']);
        DB::table('products')->insert(['id' => 1, 'name' => 'Service']);
        DB::table('order_product')->insert(['order_id' => 1, 'product_id' => 1]);
        DB::table('sms')->insert(array_merge($common, ['source' => 'cloudtalk', 'message' => 'Latest', 'created_at' => '2026-10-22 14:00:00']));
        DB::table('prospect_user')->insert(array_merge($common, ['user_id' => 2, 'created_at' => '2026-10-22 10:00:00']));
        foreach (range(1, 30) as $id) {
            DB::table('revisions')->insert([
                'revisionable_id' => 1, 'revisionable_type' => Prospect::class, 'key' => 'first_name',
                'old_value' => 'Before', 'new_value' => 'After', 'user_id' => 1,
                'created_at' => '2026-10-21 13:00:00',
            ]);
        }
    }
}
