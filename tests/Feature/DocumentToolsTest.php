<?php

namespace Tests\Feature;

use App\Agents\AgentExecutionContext;
use App\Agents\Entities\ReadGateway;
use App\Agents\Tools\DocumentGenerateTool;
use App\Agents\Tools\DocumentTemplatesTool;
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
 * Templates of documents and generation of the PDF of a prospect, for the AI agents.
 */
class DocumentToolsTest extends TestCase
{
    protected AgentExecutionContext $context;
    protected array $sent = [];
    protected bool $prospectVisible = true;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'document_tools_test',
            'database.connections.document_tools_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
        ]);

        DB::connection()->getSchemaBuilder()->create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('for')->nullable();
            $table->integer('order')->nullable();
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
        });
        DB::table('documents')->insert([
            ['id' => 1, 'project_id' => 1, 'name' => 'Contrat', 'for' => 'prospect', 'folder_id' => 5, 'description' => null, 'order' => null, 'creator_id' => null],
            ['id' => 2, 'project_id' => 1, 'name' => 'Sans dossier', 'for' => 'prospect', 'folder_id' => null, 'description' => null, 'order' => null, 'creator_id' => null],
            ['id' => 3, 'project_id' => 1, 'name' => 'Facture', 'for' => 'invoice', 'folder_id' => 5, 'description' => null, 'order' => null, 'creator_id' => null],
            ['id' => 4, 'project_id' => 2, 'name' => 'Autre projet', 'for' => 'prospect', 'folder_id' => 5, 'description' => null, 'order' => null, 'creator_id' => null],
        ]);

        $project = new Project();
        $project->id = 1;
        $this->context = new AgentExecutionContext(new User(), $project);
    }

    protected function generateTool(int $status = 200): DocumentGenerateTool
    {
        $api = Mockery::mock(InternalApi::class);
        $api->shouldReceive('call')->andReturnUsing(function ($context, $method, $path, $data, $discard = false) use ($status) {
            $this->sent[] = [$method, $path, $discard];

            return new InternalResponse($status, null, '');
        });
        $reader = Mockery::mock(ReadGateway::class);
        $reader->shouldReceive('visibleProspectIds')->andReturnUsing(
            fn ($context, array $ids) => ['ids' => $this->prospectVisible ? $ids : []]
        );

        return new DocumentGenerateTool($api, $reader);
    }

    public function test_the_list_gives_the_templates_of_the_project_and_whether_a_copy_is_kept(): void
    {
        $result = (new DocumentTemplatesTool())->handle($this->context, []);

        $this->assertSame(['Contrat', 'Facture', 'Sans dossier'], array_column($result->data['templates'], 'name'));
        $this->assertSame([true, true, false], array_column($result->data['templates'], 'keeps_a_copy'));
        $this->assertStringNotContainsString('Autre projet', json_encode($result->data));
    }

    public function test_the_list_can_be_narrowed(): void
    {
        $tool = new DocumentTemplatesTool();

        $this->assertSame([3], array_column($tool->handle($this->context, ['for' => 'invoice'])->data['templates'], 'id'));
        $this->assertSame([2], array_column($tool->handle($this->context, ['search' => 'Sans'])->data['templates'], 'id'));
    }

    public function test_the_rights_and_risks(): void
    {
        $this->assertSame('projectDocumentView', (new DocumentTemplatesTool())->ability());
        $this->assertSame(DocumentTemplatesTool::READ, (new DocumentTemplatesTool())->risk());
        $this->assertSame(DocumentGenerateTool::WRITE, $this->generateTool()->risk());
        $this->assertSame('projectDocumentView', $this->generateTool()->ability());
    }

    public function test_the_pdf_is_generated_through_the_route_and_its_temporary_file_discarded(): void
    {
        $result = $this->generateTool()->handle($this->context, ['prospect_id' => 7, 'document_id' => 1]);

        $this->assertFalse($result->isError);
        $this->assertTrue($result->data['generated']);
        $this->assertSame([['GET', 'prospect/7/document/1', true]], $this->sent);
    }

    public function test_a_template_without_folder_for_an_invoice_or_of_another_project_is_refused(): void
    {
        $expected = [2 => ToolResult::INVALID_INPUT, 3 => ToolResult::INVALID_INPUT, 4 => ToolResult::NOT_FOUND, 99 => ToolResult::NOT_FOUND];

        foreach ($expected as $id => $code) {
            $this->assertSame($code, $this->generateTool()->handle($this->context, ['prospect_id' => 7, 'document_id' => $id])->code, (string) $id);
        }
        $this->assertSame([], $this->sent);
    }

    public function test_a_prospect_the_user_may_not_see_gets_no_document(): void
    {
        $this->prospectVisible = false;

        $result = $this->generateTool()->handle($this->context, ['prospect_id' => 7, 'document_id' => 1]);

        $this->assertSame(ToolResult::NOT_FOUND, $result->code);
        $this->assertSame([], $this->sent);
    }

    public function test_an_error_of_the_crm_is_given_back(): void
    {
        $result = $this->generateTool(500)->handle($this->context, ['prospect_id' => 7, 'document_id' => 1]);

        $this->assertTrue($result->isError);
    }

    public function test_the_catalog_of_ready_made_templates_is_listed_without_touching_the_templates_of_the_project(): void
    {
        $result = (new DocumentTemplatesTool())->handle($this->context, ['catalog' => true]);

        $this->assertFalse($result->isError);
        $this->assertContains('empty', array_column($result->data['catalog'], 'key'));
        $this->assertSame('document_template_install', $result->data['install_with']);
        $this->assertArrayNotHasKey('templates', $result->data);
    }

    public function test_a_ready_made_template_is_installed_through_the_route_of_the_crm(): void
    {
        $api = Mockery::mock(InternalApi::class);
        $api->shouldReceive('call')->andReturnUsing(function ($context, $method, $path, $data = []) {
            $this->sent[] = [$method, $path];

            return new InternalResponse(200, ['id' => 55], '{}');
        });
        $tool = new \App\Agents\Tools\DocumentTemplateInstallTool($api);

        $this->assertFalse($tool->handle($this->context, ['template' => 'empty'])->isError);
        $this->assertSame([['POST', 'document-template/empty']], $this->sent);

        $this->assertSame(ToolResult::NOT_FOUND, $tool->precheck($this->context, ['template' => 'inconnu'])->code);
        $this->assertSame(ToolResult::NOT_FOUND, $tool->handle($this->context, ['template' => 'inconnu'])->code);
        $this->assertCount(1, $this->sent);
        $this->assertSame('projectDocumentAdd', $tool->ability());
        $this->assertSame(\App\Agents\Tools\DocumentTemplateInstallTool::WRITE, $tool->risk());
    }
}
