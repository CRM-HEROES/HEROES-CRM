<?php

namespace Tests\Feature;

use App\Agents\AgentExecutionContext;
use App\Agents\Tools\ToolResult;
use App\Agents\Tools\TrashItems;
use App\Agents\Tools\TrashListTool;
use App\Agents\Tools\TrashRestoreTool;
use App\Agents\Writes\InternalApi;
use App\Agents\Writes\InternalResponse;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

/**
 * Listing and restoring what is in the trash, for the AI agents.
 */
class TrashToolsTest extends TestCase
{
    protected AgentExecutionContext $context;
    protected bool $mayRestore = true;
    protected array $sent = [];

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'trash_tools_test',
            'database.connections.trash_tools_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
        ]);

        DB::connection()->getSchemaBuilder()->create('prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $now = now();
        DB::table('prospects')->insert([
            ['id' => 1, 'project_id' => 1, 'first_name' => 'Marie', 'last_name' => 'Curie', 'phone' => '+33600000001', 'deleted_at' => $now->copy()->subDay()],
            ['id' => 2, 'project_id' => 1, 'first_name' => 'Pierre', 'last_name' => 'Curie', 'phone' => '+33600000002', 'deleted_at' => $now],
            ['id' => 3, 'project_id' => 1, 'first_name' => 'Actif', 'last_name' => 'Prospect', 'phone' => null, 'deleted_at' => null],
            ['id' => 4, 'project_id' => 2, 'first_name' => 'Autre', 'last_name' => 'Projet', 'phone' => null, 'deleted_at' => $now],
        ]);

        $project = new Project();
        $project->id = 1;
        $owner = Mockery::mock(User::class)->makePartial();
        $owner->shouldReceive('can')->andReturnUsing(fn () => $this->mayRestore);
        $this->context = new AgentExecutionContext($owner, $project);
    }

    protected function restoreTool(): TrashRestoreTool
    {
        $api = Mockery::mock(InternalApi::class);
        $api->shouldReceive('call')->andReturnUsing(function ($context, $method, $path, $data) {
            $this->sent[] = [$method, $path, $data];

            return new InternalResponse(200, ['message' => 'ok'], '{}');
        });

        return new TrashRestoreTool(new TrashItems(), $api);
    }

    public function test_the_list_gives_the_deleted_prospects_of_the_project_latest_first_without_personal_data(): void
    {
        $result = (new TrashListTool(new TrashItems()))->handle($this->context, ['type' => 'prospect']);

        $this->assertFalse($result->isError);
        $this->assertSame(2, $result->data['total']);
        $this->assertSame([2, 1], array_column($result->data['items'], 'id'));
        $this->assertSame('Pierre Curie', $result->data['items'][0]['name']);
        $this->assertStringNotContainsString('+3360000', json_encode($result->data));
        $this->assertStringNotContainsString('Autre', json_encode($result->data));
    }

    public function test_the_list_can_search_by_name(): void
    {
        $result = (new TrashListTool(new TrashItems()))->handle($this->context, ['type' => 'prospect', 'search' => 'Marie']);

        $this->assertSame([1], array_column($result->data['items'], 'id'));
    }

    public function test_the_tools_have_the_right_risks(): void
    {
        $this->assertSame(TrashListTool::READ, (new TrashListTool(new TrashItems()))->risk());
        $this->assertSame(TrashRestoreTool::WRITE, $this->restoreTool()->risk());
    }

    public function test_only_what_is_in_the_trash_of_the_project_is_sent_to_the_route(): void
    {
        // 3 is not deleted, 4 belongs to another project, 99 does not exist
        $result = $this->restoreTool()->handle($this->context, ['type' => 'prospect', 'ids' => [1, 3, 4, 99]]);

        $this->assertFalse($result->isError);
        $this->assertSame([['PUT', 'trash/bulk', ['action' => 'restore', 'prospects' => [1]]]], $this->sent);
        $this->assertSame(1, $result->data['restored']);
        $this->assertSame([3, 4, 99], $result->data['not_in_trash']);
    }

    public function test_nothing_is_called_when_none_of_the_ids_is_in_the_trash(): void
    {
        $result = $this->restoreTool()->handle($this->context, ['type' => 'prospect', 'ids' => [3, 4]]);

        $this->assertTrue($result->isError);
        $this->assertSame(ToolResult::NOT_FOUND, $result->code);
        $this->assertSame([], $this->sent);
    }

    public function test_without_the_right_to_delete_nothing_is_restored(): void
    {
        $this->mayRestore = false;

        $result = $this->restoreTool()->handle($this->context, ['type' => 'prospect', 'ids' => [1]]);

        $this->assertTrue($result->isError);
        $this->assertSame(ToolResult::PERMISSION_DENIED, $result->code);
        $this->assertSame([], $this->sent);
    }

    public function test_the_number_asked_drives_the_confirmation_and_is_capped(): void
    {
        $tool = $this->restoreTool();

        $this->assertSame(3, $tool->affectedCount($this->context, ['type' => 'prospect', 'ids' => [1, 2, 2, 5, 0]]));

        $tooMany = $tool->handle($this->context, ['type' => 'prospect', 'ids' => range(1, TrashRestoreTool::MAX + 1)]);
        $this->assertSame(ToolResult::LIMIT_EXCEEDED, $tooMany->code);
    }

    public function test_the_preview_names_what_would_come_back(): void
    {
        $preview = $this->restoreTool()->preview($this->context, ['type' => 'prospect', 'ids' => [1]]);

        $this->assertSame('Marie Curie', $preview['items'][0]['name']);
    }
}
