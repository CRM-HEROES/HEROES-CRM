<?php

namespace Tests\Feature;

use App\Filters\ProspectRequestFilters;
use App\Http\Controllers\API\Project\ProspectController;
use App\Models\Project;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProspectFindReplacePaginationTest extends TestCase
{
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'prospect_find_replace_test',
            'database.connections.prospect_find_replace_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);

        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('projects', function (Blueprint $table): void {
            $table->id();
            $table->string('slug');
            $table->timestamps();
            $table->softDeletes();
        });
        $schema->create('fields', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('for');
            $table->string('slug');
            $table->boolean('meta')->default(false);
            $table->string('name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        $schema->create('prospects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('first_name')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('projects')->insert(['id' => 1, 'slug' => 'test']);
        DB::table('fields')->insert([
            'project_id' => 1,
            'for' => 'prospect',
            'slug' => 'first_name',
            'meta' => false,
            'name' => 'First name',
        ]);
        foreach (range(1, 201) as $id) {
            DB::table('prospects')->insert([
                'id' => $id,
                'project_id' => 1,
                'first_name' => "needle-$id",
            ]);
        }

        $this->project = (new Project())->forceFill(['id' => 1, 'slug' => 'test']);
        $this->project->exists = true;
    }

    protected function tearDown(): void
    {
        DB::purge('prospect_find_replace_test');
        parent::tearDown();
    }

    public function test_preview_pages_over_the_full_result_set_with_bounded_batches(): void
    {
        $first = $this->previewPage();
        $second = $this->previewPage($first['next_cursor']);
        $third = $this->previewPage($second['next_cursor']);

        $this->assertSame(100, $first['scanned']);
        $this->assertSame(100, $second['scanned']);
        $this->assertSame(1, $third['scanned']);
        $this->assertSame(100, $first['next_cursor']);
        $this->assertSame(200, $second['next_cursor']);
        $this->assertNull($third['next_cursor']);
        $this->assertSame(201, $first['matches'] + $second['matches'] + $third['matches']);
        $this->assertCount(5, $first['samples']);
    }

    private function previewPage(?int $afterId = null): array
    {
        $params = [
            'field' => 'first_name',
            'search' => 'needle',
            'mode' => 'preview',
            'limit' => 5,
            'batch_size' => 100,
        ];
        if ($afterId !== null) {
            $params['after_id'] = $afterId;
        }

        $request = Request::create('/api/project/test/prospect/find-replace', 'POST', $params);
        $this->app->instance('request', $request);
        $filters = new ProspectRequestFilters($request);

        return (new ProspectController())->findReplace($request, $filters, $this->project);
    }
}
