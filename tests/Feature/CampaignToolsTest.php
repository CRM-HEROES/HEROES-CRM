<?php

namespace Tests\Feature;

use App\Agents\AgentExecutionContext;
use App\Agents\Tools\CampaignSetActiveTool;
use App\Agents\Tools\CampaignStatusTool;
use App\Agents\Tools\ToolResult;
use App\Agents\Writes\InternalApi;
use App\Agents\Writes\InternalResponse;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

/**
 * Follow-up and switch of the historical campaigns for the AI agents.
 */
class CampaignToolsTest extends TestCase
{
    protected AgentExecutionContext $context;
    protected array $sent = [];

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'campaign_tools_test',
            'database.connections.campaign_tools_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
        ]);

        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name');
            $table->string('for')->default('prospect');
            $table->string('frequency')->nullable();
            $table->string('date')->nullable();
            $table->boolean('active')->default(false);
            $table->boolean('pending')->default(false);
            $table->boolean('processing')->default(false);
            $table->boolean('error')->default(false);
            $table->boolean('re_process_processed_prospects')->default(false);
            $table->timestamps();
        });
        $schema->create('campaign_actions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('action')->nullable();
            $table->json('value')->nullable();
            $table->timestamps();
        });
        $schema->create('campaign_action', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('action_id');
            $table->boolean('processing')->default(false);
            $table->boolean('error')->default(false);
        });
        foreach (['campaign_rules', 'campaign_operators'] as $table) {
            $schema->create($table, function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }
        DB::table('campaign_rules')->insert([['id' => 1], ['id' => 2]]);
        $schema->create('campaign_rule', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('rule_id');
        });
        $schema->create('campaign_operator', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('operator_id');
        });

        DB::table('campaigns')->insert([
            ['id' => 1, 'project_id' => 1, 'name' => 'Relance salon', 'active' => true, 'error' => false],
            ['id' => 2, 'project_id' => 1, 'name' => 'En erreur', 'active' => false, 'error' => true],
            ['id' => 3, 'project_id' => 2, 'name' => 'Autre projet', 'active' => true, 'error' => false],
        ]);
        DB::table('campaign_actions')->insert(['id' => 1, 'name' => 'SMS de relance', 'action' => 'sms', 'value' => json_encode(['body' => 'Texte secret'])]);
        DB::table('campaign_action')->insert(['campaign_id' => 1, 'action_id' => 1]);
        DB::table('campaign_rule')->insert([['campaign_id' => 1, 'rule_id' => 1], ['campaign_id' => 1, 'rule_id' => 2]]);

        $project = new Project();
        $project->id = 1;
        $this->context = new AgentExecutionContext(new User(), $project);
    }

    protected function setTool(): CampaignSetActiveTool
    {
        $api = Mockery::mock(InternalApi::class);
        $api->shouldReceive('call')->andReturnUsing(function ($context, $method, $path, $data) {
            $this->sent[] = [$method, $path, $data];

            return new InternalResponse(200, ['message' => 'ok'], '{}');
        });

        return new CampaignSetActiveTool($api);
    }

    public function test_the_list_gives_the_campaigns_of_the_project_only_and_can_keep_the_ones_in_error(): void
    {
        $all = (new CampaignStatusTool())->handle($this->context, ['view' => 'list']);
        $this->assertSame([2, 1], array_column($all->data['campaigns'], 'id'));
        $this->assertStringNotContainsString('Autre projet', json_encode($all->data));

        $errors = (new CampaignStatusTool())->handle($this->context, ['view' => 'list', 'in_error' => true]);
        $this->assertSame([2], array_column($errors->data['campaigns'], 'id'));
    }

    public function test_the_detail_names_the_actions_but_never_their_text(): void
    {
        $result = (new CampaignStatusTool())->handle($this->context, ['view' => 'detail', 'campaign_id' => 1]);

        $this->assertSame([['name' => 'SMS de relance', 'type' => 'sms']], $result->data['actions']);
        $this->assertSame(2, $result->data['rules']);
        $this->assertStringNotContainsString('secret', json_encode($result->data));
    }

    public function test_a_campaign_of_another_project_is_not_found(): void
    {
        foreach ([3, 999, 0] as $id) {
            $this->assertSame(ToolResult::NOT_FOUND, (new CampaignStatusTool())->handle($this->context, ['view' => 'detail', 'campaign_id' => $id])->code);
            $this->assertSame(ToolResult::NOT_FOUND, $this->setTool()->handle($this->context, ['campaign_id' => $id, 'active' => false])->code);
        }
        $this->assertSame([], $this->sent);
    }

    public function test_the_switch_goes_through_the_route_and_is_always_decided_by_a_human(): void
    {
        $tool = $this->setTool();

        $this->assertSame(CampaignSetActiveTool::COMMUNICATION, $tool->risk());
        $this->assertSame('projectCampaignUpdate', $tool->ability());
        $this->assertFalse($tool->handle($this->context, ['campaign_id' => 1, 'active' => false])->isError);
        $this->assertSame([['PUT', 'campaign/1', ['active' => false]]], $this->sent);
    }

    public function test_a_campaign_in_error_cannot_be_turned_on_but_can_be_turned_off(): void
    {
        $on = $this->setTool()->handle($this->context, ['campaign_id' => 2, 'active' => true]);
        $this->assertSame(ToolResult::INVALID_INPUT, $on->code);
        $this->assertSame([], $this->sent);

        $this->assertFalse($this->setTool()->handle($this->context, ['campaign_id' => 2, 'active' => false])->isError);
    }

    public function test_the_status_tool_needs_the_view_right_and_only_reads(): void
    {
        $this->assertSame('projectCampaignView', (new CampaignStatusTool())->ability());
        $this->assertSame(CampaignStatusTool::READ, (new CampaignStatusTool())->risk());
    }
}
