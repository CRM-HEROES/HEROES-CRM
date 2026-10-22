<?php

namespace Tests\Feature;

use App\Exports\ProspectDownload;
use App\Exports\ProspectExport;
use App\Jobs\ExportProspects;
use App\Jobs\PrepareProspectDownload;
use App\Models\Export;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\User;
use App\Notifications\ExportFinished;
use App\Services\ProspectHistory;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ProspectHistoryExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake([Authenticated::class]);
        $this->app->setLocale('fr');
        config([
            'database.default' => 'prospect_history_export_test',
            'database.connections.prospect_history_export_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);

        $tables = [
            'projects' => ['slug', 'name'],
            'prospects' => ['project_id', 'first_name'],
            'users' => ['name', 'role', 'email'],
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
        $user->setRawAttributes(['id' => 1, 'name' => 'Actor', 'role' => 'super_admin', 'email' => 'actor@example.test']);
        $user->shouldReceive('can')->with('prospectEventView', Mockery::type(Project::class))->andReturn(true);
        $this->actingAs($user);
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Actor', 'role' => 'super_admin', 'email' => 'actor@example.test'],
            ['id' => 2, 'name' => 'Assigned user', 'role' => 'user', 'email' => 'assigned@example.test'],
        ]);
        DB::table('projects')->insert(['id' => 1, 'slug' => 'history-export-test', 'name' => 'History export']);
        DB::table('prospects')->insert([
            ['id' => 1, 'project_id' => 1, 'first_name' => 'Alpha', 'created_at' => '2026-10-01 00:00:00'],
            ['id' => 2, 'project_id' => 1, 'first_name' => 'Beta', 'created_at' => '2026-10-01 00:00:00'],
        ]);
        DB::table('fields')->insert(['project_id' => 1, 'for' => 'prospect', 'slug' => 'first_name', 'meta' => 0, 'name' => 'Prénom']);
        DB::table('categories')->insert(['id' => 1, 'project_id' => 1, 'for' => 'prospect', 'name' => 'Statut']);
        DB::table('calendars')->insert(['id' => 1, 'project_id' => 1, 'name' => 'Rendez-vous']);
        DB::table('threads')->insert(['id' => 1, 'project_id' => 1, 'name' => 'Suivi prospect']);
        DB::table('folders')->insert(['id' => 1, 'project_id' => 1, 'for' => 'prospect', 'name' => 'Documents']);
        DB::table('user_folder')->insert(['folder_id' => 1, 'user_id' => 1]);
    }

    protected function tearDown(): void
    {
        DB::purge('prospect_history_export_test');
        parent::tearDown();
    }

    public function test_collection_exports_complete_history_with_one_row_per_prospect_and_selected_column_order(): void
    {
        $this->seedHistory();
        $export = new ProspectExport($this->export());
        $rows = $export->collection()->values()->all();

        $this->assertExportRows($rows);
        $this->assertSame(2, $export->getCount());
    }

    public function test_exports_without_history_do_not_load_the_timeline(): void
    {
        $this->seedHistory();
        $service = Mockery::mock(ProspectHistory::class);
        $service->shouldNotReceive('append');
        $service->shouldNotReceive('appendSummary');
        $this->app->instance(ProspectHistory::class, $service);

        DB::enableQueryLog();
        try {
            $rows = (new ProspectExport($this->export(['first_name'])))->collection()->values()->all();
            $queries = collect(DB::getQueryLog())->pluck('query')->implode("\n");
        } finally {
            DB::disableQueryLog();
        }

        $this->assertSame([['Prénom'], ['Beta'], ['Alpha']], $rows);
        $this->assertStringNotContainsString('revisions', $queries);
        $this->assertStringNotContainsString('messages', $queries);
        $this->assertStringNotContainsString('sms', $queries);
    }

    public function test_browser_download_contains_complete_history_in_a_single_xlsx_cell(): void
    {
        $this->seedHistory();
        $response = (new ProspectDownload())->download(new ProspectExport($this->export()), 'Prospects.xlsx');
        $path = $response->getFile()->getPathname();

        try {
            $this->assertExportRows($this->readXlsx($path));
        } finally {
            unlink($path);
        }
    }

    public function test_prepared_download_contains_complete_history_and_preserves_prospect_limit(): void
    {
        $this->seedHistory();
        Storage::fake('exports');
        $export = $this->export();
        $export->count = 1;
        // Keep one prospect with a long history in the bounded export.
        DB::table('prospects')->where('id', 2)->delete();
        $download = new ProspectDownload();

        (new PrepareProspectDownload($export))->handle($download);

        $this->assertSame('ready', $download->status($export)['status']);
        $this->assertSame(1, $download->status($export)['rows']);
        $rows = $this->readXlsx(Storage::disk('exports')->path('downloads/123.xlsx'));
        $this->assertCount(2, $rows);
        $this->assertSame(['Prénom', 'Historique'], $rows[0]);
        $this->assertSame('Alpha', $rows[1][0]);
        $this->assertCompleteHistory($rows[1][1]);
    }

    public function test_email_export_writes_complete_history_through_collection_writer(): void
    {
        $this->seedHistory();
        Storage::fake('exports');
        Mail::fake();

        (new ExportProspects($this->export()))->handle();

        Storage::disk('exports')->assertExists('123-1.xlsx');
        $this->assertExportRows($this->readXlsx(Storage::disk('exports')->path('123-1.xlsx')));
        Mail::assertSent(ExportFinished::class);
    }

    protected function assertExportRows(array $rows): void
    {
        $this->assertCount(3, $rows);
        $this->assertSame(['Prénom', 'Historique'], $rows[0]);
        $this->assertSame(['Beta', ''], $rows[1]);
        $this->assertSame('Alpha', $rows[2][0]);
        $this->assertCompleteHistory($rows[2][1]);
    }

    protected function assertCompleteHistory(string $cell): void
    {
        foreach (['event', 'file', 'group', 'interaction', 'label', 'link', 'message', 'order', 'revision', 'sms', 'user'] as $type) {
            $this->assertStringContainsString(trans('prospect.export.history_types.' . $type), $cell);
        }
        foreach (range(1, 30) as $id) {
            $prefix = sprintf('REV_%02d_', $id);
            $this->assertStringContainsString($prefix . 'OLD → ' . $prefix . 'NEW', $cell);
        }
        foreach (['Actor', 'Prénom', 'Latest_sms', 'Document.pdf', 'Sales', 'New', 'https://example.test', 'Suivi complet', 'Deuxième ligne', 'Service', 'Assigned user'] as $value) {
            $this->assertStringContainsString($value, $cell);
        }
        $this->assertStringContainsString("\n", $cell);
        $this->assertStringNotContainsString('<p>', $cell);
        $this->assertLessThan(strpos($cell, 'REV_30_NEW'), strpos($cell, 'Latest_sms'));
        $this->assertLessThan(strpos($cell, 'REV_01_NEW'), strpos($cell, 'REV_30_NEW'));
    }

    protected function readXlsx(string $path): array
    {
        $reader = ReaderEntityFactory::createXLSXReader();
        $rows = [];

        try {
            $reader->open($path);
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rows[] = $row->toArray();
                }
            }
        } finally {
            $reader->close();
        }

        return $rows;
    }

    protected function export(array $fields = ['first_name', 'history']): Export
    {
        return (new Export())->forceFill([
            'id' => 123, 'project_id' => 1, 'creator_id' => 1, 'fields' => $fields, 'filters' => [],
            'sort_by' => 'id', 'sort_order' => 'desc', 'count' => 0, 'created_at' => '2026-10-23 00:00:00',
        ])->setRelation('project', Project::query()->findOrFail(1))->setRelation('creator', auth()->user());
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
        DB::table('messages')->insert($common + ['id' => 1, 'thread_id' => 1, 'body' => '<p>Suivi complet</p><p>Deuxième ligne</p>']);
        DB::table('orders')->insert($common + ['name' => 'Quote']);
        DB::table('products')->insert(['id' => 1, 'name' => 'Service']);
        DB::table('order_product')->insert(['order_id' => 1, 'product_id' => 1]);
        DB::table('sms')->insert(array_merge($common, ['source' => 'cloudtalk', 'message' => 'Latest_sms', 'created_at' => '2026-10-22 14:00:00']));
        DB::table('prospect_user')->insert(array_merge($common, ['user_id' => 2, 'created_at' => '2026-10-22 10:00:00']));
        foreach (range(1, 30) as $id) {
            $prefix = sprintf('REV_%02d_', $id);
            DB::table('revisions')->insert([
                'revisionable_id' => 1, 'revisionable_type' => Prospect::class, 'key' => 'first_name',
                'old_value' => $prefix . 'OLD', 'new_value' => $prefix . 'NEW', 'user_id' => 1,
                'created_at' => '2026-10-21 13:00:00',
            ]);
        }
    }
}
