<?php

namespace Tests\Feature;

use App\Agents\AgentExecutionContext;
use App\Agents\Tools\MessageTemplatesTool;
use App\Agents\Tools\PipelineOverviewTool;
use App\Agents\Tools\ToolResult;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Text templates and pipelines for the AI agents.
 */
class TemplateAndPipelineToolsTest extends TestCase
{
    protected AgentExecutionContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'template_pipeline_test',
            'database.connections.template_pipeline_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
        ]);

        $schema = DB::connection()->getSchemaBuilder();
        foreach (['sms_templates', 'message_templates', 'event_description_templates'] as $table) {
            $schema->create($table, function (Blueprint $t) use ($table) {
                $t->id();
                $t->unsignedBigInteger('project_id');
                $t->string('name');
                $t->text('body')->nullable();
                $t->integer('order')->nullable();
                if ($table === 'message_templates') {
                    $t->string('subject')->nullable();
                    $t->json('attachments')->nullable();
                    $t->json('email_layout')->nullable();
                }
                $t->timestamps();
                $t->softDeletes();
            });
        }
        DB::table('sms_templates')->insert([
            ['project_id' => 1, 'name' => 'Relance', 'body' => 'Bonjour {{first_name}}', 'order' => 1],
            ['project_id' => 2, 'name' => 'Autre projet', 'body' => 'x', 'order' => 1],
        ]);
        DB::table('message_templates')->insert([
            ['project_id' => 1, 'name' => 'Devis', 'subject' => 'Votre devis', 'body' => 'Voir pièce jointe', 'order' => 1, 'attachments' => json_encode([['name' => 'secret.pdf', 'path' => 'a/b.pdf'], ['name' => 'x.pdf', 'path' => 'c.pdf']])],
        ]);

        $schema->create('pipelines', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('project_id');
            $t->unsignedBigInteger('creator_id')->nullable();
            $t->string('name');
            $t->text('description')->nullable();
            $t->string('color')->nullable();
            $t->string('bgcolor')->nullable();
            $t->integer('order')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        $schema->create('labels', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('category_id')->nullable();
            $t->string('name');
            $t->timestamps();
            $t->softDeletes();
        });
        $schema->create('pipeline_label', function (Blueprint $t) {
            $t->unsignedBigInteger('pipeline_id');
            $t->unsignedBigInteger('label_id');
            $t->integer('order')->nullable();
        });
        $schema->create('prospects', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('project_id');
            $t->timestamp('processed_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        $schema->create('prospect_label', function (Blueprint $t) {
            $t->unsignedBigInteger('prospect_id');
            $t->unsignedBigInteger('label_id');
        });

        DB::table('pipelines')->insert([
            ['id' => 1, 'project_id' => 1, 'name' => 'Vente'],
            ['id' => 2, 'project_id' => 2, 'name' => 'Autre pipeline'],
        ]);
        DB::table('labels')->insert([['id' => 10, 'name' => 'Nouveau'], ['id' => 11, 'name' => 'Gagné']]);
        DB::table('pipeline_label')->insert([
            ['pipeline_id' => 1, 'label_id' => 11, 'order' => 2],
            ['pipeline_id' => 1, 'label_id' => 10, 'order' => 1],
        ]);
        DB::table('prospects')->insert([
            ['id' => 1, 'project_id' => 1, 'processed_at' => null, 'deleted_at' => null],
            ['id' => 2, 'project_id' => 1, 'processed_at' => null, 'deleted_at' => null],
            ['id' => 3, 'project_id' => 1, 'processed_at' => now(), 'deleted_at' => null],
            ['id' => 4, 'project_id' => 1, 'processed_at' => null, 'deleted_at' => now()],
            ['id' => 5, 'project_id' => 2, 'processed_at' => null, 'deleted_at' => null],
        ]);
        DB::table('prospect_label')->insert([
            ['prospect_id' => 1, 'label_id' => 10], ['prospect_id' => 2, 'label_id' => 10],
            ['prospect_id' => 3, 'label_id' => 10], ['prospect_id' => 4, 'label_id' => 10],
            ['prospect_id' => 5, 'label_id' => 10], ['prospect_id' => 2, 'label_id' => 11],
        ]);

        $project = new Project();
        $project->id = 1;
        $this->context = new AgentExecutionContext(new User(), $project);
    }

    public function test_the_templates_of_a_kind_are_read_for_the_project_only(): void
    {
        $result = (new MessageTemplatesTool())->handle($this->context, ['kind' => 'sms']);

        $this->assertSame(['Relance'], array_column($result->data['templates'], 'name'));
        $this->assertSame('Bonjour {{first_name}}', $result->data['templates'][0]['body']);
        $this->assertStringNotContainsString('Autre projet', json_encode($result->data));
    }

    public function test_an_email_template_gives_the_subject_and_only_counts_its_attachments(): void
    {
        $result = (new MessageTemplatesTool())->handle($this->context, ['kind' => 'email']);

        $this->assertSame('Votre devis', $result->data['templates'][0]['subject']);
        $this->assertSame(2, $result->data['templates'][0]['attachments']);
        $this->assertStringNotContainsString('secret.pdf', json_encode($result->data));
    }

    public function test_a_template_can_be_searched_by_name_and_an_empty_kind_gives_nothing(): void
    {
        $tool = new MessageTemplatesTool();

        $this->assertSame([], $tool->handle($this->context, ['kind' => 'sms', 'search' => 'zzz'])->data['templates']);
        $this->assertSame([], $tool->handle($this->context, ['kind' => 'appointment'])->data['templates']);
    }

    public function test_the_pipelines_are_listed_with_their_stages_in_order(): void
    {
        $result = (new PipelineOverviewTool())->handle($this->context, []);

        $this->assertSame(['Vente'], array_column($result->data['pipelines'], 'name'));
        $this->assertSame(['Nouveau', 'Gagné'], array_column($result->data['pipelines'][0]['stages'], 'name'));
    }

    public function test_the_counts_skip_processed_deleted_and_other_project_prospects(): void
    {
        $result = (new PipelineOverviewTool())->handle($this->context, ['pipeline_id' => 1]);

        $this->assertFalse($result->isError);
        $this->assertSame([2, 1], array_column($result->data['stages'], 'prospects'));
        $this->assertSame(3, $result->data['total']);
    }

    public function test_a_pipeline_of_another_project_is_not_found(): void
    {
        foreach ([2, 99] as $id) {
            $this->assertSame(ToolResult::NOT_FOUND, (new PipelineOverviewTool())->handle($this->context, ['pipeline_id' => $id])->code);
        }
    }

    public function test_both_tools_only_read(): void
    {
        $this->assertSame(MessageTemplatesTool::READ, (new MessageTemplatesTool())->risk());
        $this->assertSame(PipelineOverviewTool::READ, (new PipelineOverviewTool())->risk());
    }
}
