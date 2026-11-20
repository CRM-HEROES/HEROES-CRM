<?php

namespace Tests\Feature;

use App\Agents\AgentExecutionContext;
use App\Agents\Tools\ProjectEntities;
use App\Agents\Tools\ProjectEntityDeleteTool;
use App\Agents\Tools\ProjectEntitySaveTool;
use App\Agents\Tools\ToolResult;
use App\Agents\Writes\InternalApi;
use App\Agents\Writes\InternalResponse;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\UsesInMemoryDatabase;
use Tests\TestCase;

/**
 * Creating, changing and deleting the configuration of a project for the AI agents: texts, filters, groups, pipelines,
 * folders, custom fields.
 */
class ProjectEntityToolsTest extends TestCase
{
    use UsesInMemoryDatabase;

    protected int $pid;
    protected int $otherPid;
    protected array $u = [];
    protected array $sent = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpInMemoryDatabase();
        config(['logging.default' => 'null']);
        Event::fake([Authenticated::class]);

        foreach (['sms_templates', 'message_templates', 'event_description_templates', 'groups', 'pipelines', 'folders'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('project_id');
                $t->string('name')->nullable();
                $t->timestamps();
                $t->softDeletes();
            });
        }
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('project_id');
            $t->string('name')->nullable();
            $t->string('for')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('labels', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('category_id');
            $t->string('name')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('fields', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('project_id');
            $t->string('name')->nullable();
            $t->string('slug')->nullable();
            $t->string('for')->default('prospect');
            $t->boolean('meta')->default(false);
            $t->timestamps();
            $t->softDeletes();
        });

        foreach (['creator', 'bob'] as $name) {
            $this->u[$name] = $this->makeUser(ucfirst($name));
        }
        $this->pid = $this->makeProject('Alpha');
        $this->otherPid = $this->makeProject('Beta');
        DB::table('projects')->where('id', $this->pid)->update(['creator_id' => $this->u['creator']]);
        foreach ($this->u as $id) {
            DB::table('user_project')->insert(['user_id' => $id, 'project_id' => $this->pid]);
        }

        $now = now();
        DB::table('sms_templates')->insert([['id' => 1, 'project_id' => $this->pid, 'name' => 'Relance', 'created_at' => $now, 'updated_at' => $now], ['id' => 2, 'project_id' => $this->otherPid, 'name' => 'Autre', 'created_at' => $now, 'updated_at' => $now]]);
        DB::table('categories')->insert([['id' => 5, 'project_id' => $this->pid, 'name' => 'Origine', 'for' => 'prospect', 'created_at' => $now, 'updated_at' => $now], ['id' => 6, 'project_id' => $this->otherPid, 'name' => 'Autre', 'for' => 'prospect', 'created_at' => $now, 'updated_at' => $now]]);
        DB::table('labels')->insert([['id' => 9, 'category_id' => 5, 'name' => 'Salon', 'created_at' => $now, 'updated_at' => $now], ['id' => 10, 'category_id' => 6, 'name' => 'Autre', 'created_at' => $now, 'updated_at' => $now]]);
        DB::table('fields')->insert([
            ['id' => 20, 'project_id' => $this->pid, 'name' => 'Ville', 'slug' => 'city', 'for' => 'prospect', 'meta' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 21, 'project_id' => $this->pid, 'name' => 'Origine', 'slug' => 'origine', 'for' => 'prospect', 'meta' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function ctx(string $who): AgentExecutionContext
    {
        $project = Project::withoutGlobalScopes()->find($this->pid);
        $owner = User::find($this->u[$who]);
        auth()->setUser($owner);
        setPermissionsTeamId($this->pid);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        request()->merge(['project' => $project]);

        return new AgentExecutionContext($owner, $project);
    }

    protected function api(): InternalApi
    {
        $api = Mockery::mock(InternalApi::class);
        $api->shouldReceive('call')->andReturnUsing(function ($context, $method, $path, $data = []) {
            $this->sent[] = [$method, $path, $data];

            return new InternalResponse(200, ['id' => 77], '{}');
        });

        return $api;
    }

    protected function saver(): ProjectEntitySaveTool
    {
        return new ProjectEntitySaveTool($this->api(), new ProjectEntities());
    }

    protected function deleter(): ProjectEntityDeleteTool
    {
        return new ProjectEntityDeleteTool($this->api(), new ProjectEntities());
    }

    public function test_a_text_is_created_with_plain_values_and_its_id_is_given_back(): void
    {
        $result = $this->saver()->handle($this->ctx('creator'), ['entity' => 'sms_template', 'values' => ['name' => '<b>Rappel</b>', 'body' => 'Bonjour {{prospect.firstname}}']]);

        $this->assertFalse($result->isError);
        $this->assertSame(['done' => true, 'entity' => 'sms_template', 'id' => 77, 'created' => true], $result->data);
        $this->assertSame([['POST', 'sms-template', ['name' => 'Rappel', 'body' => 'Bonjour {{prospect.firstname}}']]], $this->sent);
    }

    public function test_the_html_of_an_email_template_is_kept_but_not_its_name(): void
    {
        $this->saver()->handle($this->ctx('creator'), ['entity' => 'message_template', 'values' => ['name' => '<i>Devis</i>', 'subject' => 'Votre devis', 'body' => '<p>Voir <b>ci-joint</b></p>']]);

        $this->assertSame(['name' => 'Devis', 'subject' => 'Votre devis', 'body' => '<p>Voir <b>ci-joint</b></p>'], $this->sent[0][2]);
    }

    public function test_a_category_is_always_for_prospects_and_a_label_goes_under_its_category(): void
    {
        $this->saver()->handle($this->ctx('creator'), ['entity' => 'category', 'values' => ['name' => 'Statut', 'unique' => true]]);
        $this->saver()->handle($this->ctx('creator'), ['entity' => 'label', 'parent_id' => 5, 'values' => ['name' => 'Nouveau', 'color' => '#000000', 'bgcolor' => '#FFFF00']]);

        $this->assertSame(['POST', 'category', ['name' => 'Statut', 'unique' => true, 'for' => 'prospect']], $this->sent[0]);
        $this->assertSame(['POST', 'category/5/label', ['name' => 'Nouveau', 'color' => '#000000', 'bgcolor' => '#FFFF00']], $this->sent[1]);
    }

    public function test_a_custom_field_is_created_as_such_and_only_custom_fields_change(): void
    {
        $this->saver()->handle($this->ctx('creator'), ['entity' => 'field', 'values' => ['name' => 'Budget', 'required' => true]]);
        $this->assertSame(['POST', 'field', ['name' => 'Budget', 'required' => true, 'for' => 'prospect', 'type' => 'text', 'meta' => true]], $this->sent[0]);

        $this->assertFalse($this->saver()->handle($this->ctx('creator'), ['entity' => 'field', 'id' => 21, 'values' => ['name' => 'Origine du lead']])->isError);
        $native = $this->saver()->handle($this->ctx('creator'), ['entity' => 'field', 'id' => 20, 'values' => ['name' => 'Ville modifiée']]);
        $this->assertSame(ToolResult::NOT_FOUND, $native->code);
        $this->assertCount(2, $this->sent);
    }

    public function test_a_change_goes_through_the_route_of_the_item(): void
    {
        $this->saver()->handle($this->ctx('creator'), ['entity' => 'sms_template', 'id' => 1, 'values' => ['body' => 'Nouveau texte']]);
        $this->saver()->handle($this->ctx('creator'), ['entity' => 'label', 'id' => 9, 'parent_id' => 5, 'values' => ['name' => 'Salon avril']]);

        $this->assertSame([['PUT', 'sms-template/1', ['body' => 'Nouveau texte']], ['PUT', 'category/5/label/9', ['name' => 'Salon avril']]], $this->sent);
    }

    public function test_wrong_values_change_nothing(): void
    {
        $cases = [
            'champ inconnu' => ['entity' => 'group', 'values' => ['name' => 'A', 'secret' => 'x']],
            'couleur invalide' => ['entity' => 'group', 'values' => ['name' => 'A', 'color' => 'rouge']],
            'nom manquant' => ['entity' => 'group', 'values' => ['color' => '#000000']],
            'texte trop long' => ['entity' => 'sms_template', 'values' => ['name' => 'A', 'body' => str_repeat('x', 1001)]],
            'rien à modifier' => ['entity' => 'group', 'id' => 1, 'values' => []],
            'label sans couleur' => ['entity' => 'label', 'parent_id' => 5, 'values' => ['name' => 'X']],
        ];

        foreach ($cases as $name => $input) {
            $this->assertSame(ToolResult::VALIDATION_FAILED, $this->saver()->handle($this->ctx('creator'), $input)->code, $name);
        }
        $this->assertSame([], $this->sent);
    }

    public function test_an_item_or_a_category_of_another_project_is_not_found(): void
    {
        $cases = [
            ['entity' => 'sms_template', 'id' => 2, 'values' => ['body' => 'x']],
            ['entity' => 'label', 'id' => 10, 'parent_id' => 6, 'values' => ['name' => 'x']],
            ['entity' => 'label', 'parent_id' => 6, 'values' => ['name' => 'x', 'color' => '#000000', 'bgcolor' => '#FFFFFF']],
            ['entity' => 'sms_template', 'id' => 999, 'values' => ['body' => 'x']],
        ];

        foreach ($cases as $i => $input) {
            $this->assertSame(ToolResult::NOT_FOUND, $this->saver()->handle($this->ctx('creator'), $input)->code, (string) $i);
        }
        $this->assertSame([], $this->sent);
    }

    public function test_the_right_of_each_kind_is_asked_when_the_crm_asks_one(): void
    {
        $denied = $this->saver()->handle($this->ctx('bob'), ['entity' => 'sms_template', 'values' => ['name' => 'A', 'body' => 'B']]);
        $this->assertSame(ToolResult::PERMISSION_DENIED, $denied->code);
        $this->assertSame(ToolResult::PERMISSION_DENIED, $this->saver()->handle($this->ctx('bob'), ['entity' => 'group', 'values' => ['name' => 'A']])->code);
        $this->assertSame(ToolResult::PERMISSION_DENIED, $this->deleter()->handle($this->ctx('bob'), ['entity' => 'sms_template', 'id' => 1])->code);
        $this->assertSame([], $this->sent);

        // The CRM asks no right for the texts of e-mails: the tool does not invent one
        $this->assertFalse($this->saver()->handle($this->ctx('bob'), ['entity' => 'message_template', 'values' => ['name' => 'A', 'body' => 'B']])->isError);
    }

    public function test_a_deletion_goes_through_the_route_and_a_label_needs_its_category(): void
    {
        $this->assertFalse($this->deleter()->handle($this->ctx('creator'), ['entity' => 'sms_template', 'id' => 1])->isError);
        $this->assertFalse($this->deleter()->handle($this->ctx('creator'), ['entity' => 'label', 'id' => 9, 'parent_id' => 5])->isError);
        $this->assertSame([['DELETE', 'sms-template/1', []], ['DELETE', 'category/5/label/9', []]], $this->sent);

        $this->assertSame(ToolResult::VALIDATION_FAILED, $this->deleter()->handle($this->ctx('creator'), ['entity' => 'label', 'id' => 9])->code);
        $this->assertSame(ToolResult::NOT_FOUND, $this->deleter()->handle($this->ctx('creator'), ['entity' => 'sms_template', 'id' => 2])->code);
        $this->assertSame(ToolResult::NOT_FOUND, $this->deleter()->handle($this->ctx('creator'), ['entity' => 'field', 'id' => 20])->code, 'Un champ natif ne se supprime pas');
        $this->assertCount(2, $this->sent);
    }

    public function test_a_deletion_is_always_decided_by_a_human_and_a_save_never_deletes(): void
    {
        $this->assertSame(ProjectEntityDeleteTool::DESTRUCTIVE, $this->deleter()->risk());
        $this->assertSame(ProjectEntitySaveTool::WRITE, $this->saver()->risk());
    }

    public function test_the_checks_come_before_the_human_is_asked(): void
    {
        $this->assertSame(ToolResult::VALIDATION_FAILED, $this->saver()->precheck($this->ctx('creator'), ['entity' => 'group', 'values' => ['color' => '#000000']])->code);
        $this->assertNull($this->saver()->precheck($this->ctx('creator'), ['entity' => 'group', 'values' => ['name' => 'Équipe B']]));
        $this->assertSame(ToolResult::NOT_FOUND, $this->deleter()->precheck($this->ctx('creator'), ['entity' => 'sms_template', 'id' => 999])->code);
        $this->assertNull($this->deleter()->precheck($this->ctx('creator'), ['entity' => 'sms_template', 'id' => 1]));
        $this->assertSame([], $this->sent);
    }

    public function test_the_preview_names_what_will_go(): void
    {
        $preview = $this->deleter()->preview($this->ctx('creator'), ['entity' => 'sms_template', 'id' => 1]);

        $this->assertSame('Relance', $preview['nom']);
        $this->assertSame('Modifier : groupe', $this->saver()->preview($this->ctx('creator'), ['entity' => 'group', 'id' => 3, 'values' => []])['action']);
        $this->assertSame('Créer : groupe', $this->saver()->preview($this->ctx('creator'), ['entity' => 'group', 'values' => []])['action']);
    }

    public function test_the_schema_offers_every_kind_and_every_field(): void
    {
        $schema = $this->saver()->inputSchema();

        $this->assertSame(ProjectEntities::kinds(), $schema['properties']['entity']['enum']);
        foreach (['name', 'body', 'subject', 'color', 'bgcolor', 'unique', 'private', 'required', 'searchable', 'default_value', 'description'] as $field) {
            $this->assertArrayHasKey($field, $schema['properties']['values']['properties'], $field);
        }
        $this->assertSame(20000, $schema['properties']['values']['properties']['body']['maxLength']);
    }

    public function test_a_document_template_takes_a_destination_and_a_folder_of_the_project(): void
    {
        DB::table('folders')->insert([['id' => 3, 'project_id' => $this->pid, 'name' => 'Contrats', 'created_at' => now(), 'updated_at' => now()], ['id' => 4, 'project_id' => $this->otherPid, 'name' => 'Autre', 'created_at' => now(), 'updated_at' => now()]]);

        $ok = $this->saver()->handle($this->ctx('creator'), ['entity' => 'document', 'values' => ['name' => 'Contrat', 'for' => 'prospect', 'folder_id' => 3]]);
        $this->assertFalse($ok->isError);
        $this->assertSame(['POST', 'document', ['name' => 'Contrat', 'for' => 'prospect', 'folder_id' => 3]], $this->sent[0]);

        $this->assertSame(ToolResult::VALIDATION_FAILED, $this->saver()->handle($this->ctx('creator'), ['entity' => 'document', 'values' => ['name' => 'X', 'for' => 'pigeon']])->code);
        $this->assertSame(ToolResult::VALIDATION_FAILED, $this->saver()->handle($this->ctx('creator'), ['entity' => 'document', 'values' => ['name' => 'X', 'folder_id' => 'abc']])->code);
        $this->assertSame(ToolResult::NOT_FOUND, $this->saver()->handle($this->ctx('creator'), ['entity' => 'document', 'values' => ['name' => 'X', 'folder_id' => 4]])->code);
        $this->assertCount(1, $this->sent);

        $schema = $this->saver()->inputSchema()['properties']['values']['properties'];
        $this->assertSame(['prospect', 'order', 'invoice'], $schema['for']['enum']);
        $this->assertSame('integer', $schema['folder_id']['type']);
    }

    public function test_the_quote_and_calendar_kinds_have_their_own_route_and_right(): void
    {
        $expected = [
            'order_status' => ['order-status', 'projectOrderStatusAdd'],
            'order_action' => ['order-action', 'projectOrderActionAdd'],
            'order_step' => ['order-step', 'projectOrderStepAdd'],
            'calendar' => ['calendar', 'projectCalendarAdd'],
            'questionnaire' => ['questionnaire', 'projectQuestionnaireAdd'],
        ];

        foreach ($expected as $kind => [$route, $right]) {
            $this->assertSame($route, ProjectEntities::KINDS[$kind]['route'], $kind);
            $this->assertSame($right, ProjectEntities::KINDS[$kind]['rights']['create'], $kind);

            $this->sent = [];
            $this->assertFalse($this->saver()->handle($this->ctx('creator'), ['entity' => $kind, 'values' => ['name' => 'Nouveau']])->isError, $kind);
            $this->assertSame([['POST', $route, ['name' => 'Nouveau']]], $this->sent, $kind);
            $this->assertSame(ToolResult::PERMISSION_DENIED, $this->saver()->handle($this->ctx('bob'), ['entity' => $kind, 'values' => ['name' => 'Nouveau']])->code, $kind);
        }
    }

    public function test_every_kind_names_a_model_with_the_columns_the_tool_reads(): void
    {
        foreach (ProjectEntities::KINDS as $kind => $spec) {
            $this->assertTrue(class_exists($spec['model']), $kind);
            $this->assertNotEmpty($spec['required'], $kind);
            $this->assertSame(['create', 'update', 'delete'], array_keys($spec['rights']), $kind);
        }
    }
}
