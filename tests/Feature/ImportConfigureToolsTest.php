<?php

namespace Tests\Feature;

use App\Agents\AgentExecutionContext;
use App\Agents\Tools\ImportConfigureTool;
use App\Agents\Tools\ImportMappingTool;
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
 * Reading and setting up the mapping of an import, for the AI agents.
 */
class ImportConfigureToolsTest extends TestCase
{
    protected AgentExecutionContext $context;
    protected array $sent = [];
    protected array $failing = [];

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'import_configure_test',
            'database.connections.import_configure_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
        ]);

        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('imports', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('project_id');
            $t->string('name');
            $t->string('source')->default('file');
            $t->json('headers')->nullable();
            $t->json('mapping')->nullable();
            $t->json('duplicates_fields')->nullable();
            $t->json('labels')->nullable();
            $t->json('groups')->nullable();
            $t->boolean('is_processing')->default(false);
            $t->timestamps();
            $t->softDeletes();
        });
        $schema->create('fields', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('project_id');
            $t->string('slug');
            $t->string('name');
            $t->string('for')->default('prospect');
            $t->boolean('meta')->default(false);
            $t->integer('order')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        foreach (['categories' => ['for'], 'threads' => []] as $table => $extra) {
            $schema->create($table, function (Blueprint $t) use ($extra) {
                $t->id();
                $t->unsignedBigInteger('project_id');
                $t->string('name');
                $t->string('for')->nullable();
                $t->timestamps();
                $t->softDeletes();
            });
        }
        $schema->create('labels', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('category_id');
            $t->string('name');
            $t->timestamps();
            $t->softDeletes();
        });
        $schema->create('groups', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('project_id');
            $t->string('name');
            $t->timestamps();
            $t->softDeletes();
        });

        $blank = ['mapping' => null, 'duplicates_fields' => null, 'labels' => null, 'groups' => null, 'is_processing' => false];
        DB::table('imports')->insert([
            ['id' => 1, 'project_id' => 1, 'name' => 'Salon', 'headers' => json_encode(['Prénom', 'Nom', 'Ville', 'Catégorie', 'Rien', '']), 'mapping' => json_encode(['first_name', 'last_name', 'meta->ville', 'category->7', null, null]), 'duplicates_fields' => json_encode([2]), 'labels' => json_encode([5]), 'groups' => json_encode([3]), 'is_processing' => false],
            ['id' => 2, 'project_id' => 1, 'name' => 'En cours', 'headers' => json_encode(['A']), 'is_processing' => true] + $blank,
            ['id' => 3, 'project_id' => 2, 'name' => 'Autre projet', 'headers' => json_encode(['A'])] + $blank,
        ]);
        DB::table('fields')->insert([
            ['id' => 1, 'project_id' => 1, 'slug' => 'first_name', 'name' => 'Prénom', 'meta' => 0],
            ['id' => 2, 'project_id' => 1, 'slug' => 'email', 'name' => 'E-mail', 'meta' => 0],
            ['id' => 3, 'project_id' => 1, 'slug' => 'ville', 'name' => 'Ville', 'meta' => 1],
            ['id' => 4, 'project_id' => 2, 'slug' => 'secret', 'name' => 'Autre projet', 'meta' => 0],
        ]);
        DB::table('categories')->insert([['id' => 7, 'project_id' => 1, 'name' => 'Origine', 'for' => 'prospect'], ['id' => 8, 'project_id' => 2, 'name' => 'Autre', 'for' => 'prospect']]);
        DB::table('threads')->insert([['id' => 4, 'project_id' => 1, 'name' => 'Support'], ['id' => 9, 'project_id' => 2, 'name' => 'Autre']]);
        DB::table('labels')->insert([['id' => 5, 'category_id' => 7, 'name' => 'Salon avril'], ['id' => 6, 'category_id' => 8, 'name' => 'Autre projet']]);
        DB::table('groups')->insert([['id' => 3, 'project_id' => 1, 'name' => 'Equipe A'], ['id' => 4, 'project_id' => 2, 'name' => 'Autre groupe']]);

        $project = new Project();
        $project->id = 1;
        $this->context = new AgentExecutionContext(new User(), $project);
    }

    protected function configure(): ImportConfigureTool
    {
        $api = Mockery::mock(InternalApi::class);
        $api->shouldReceive('call')->andReturnUsing(function ($context, $method, $path, $data = []) {
            $this->sent[] = [$method, $path, $data];
            $status = in_array($path, $this->failing, true) ? 404 : 200;

            return new InternalResponse($status, ['message' => 'ok'], '{}');
        });

        return new ImportConfigureTool($api);
    }

    public function test_the_mapping_says_what_each_column_fills_and_what_is_not_linked(): void
    {
        $result = (new ImportMappingTool())->handle($this->context, ['import_id' => 1]);

        $this->assertFalse($result->isError);
        $targets = array_column($result->data['columns'], 'target', 'column');
        $this->assertSame(['type' => 'field', 'slug' => 'first_name', 'name' => 'Prénom'], $targets[0]);
        $this->assertSame(['type' => 'meta', 'slug' => 'ville', 'name' => 'Ville'], $targets[2]);
        $this->assertSame(['type' => 'category', 'id' => 7, 'name' => 'Origine'], $targets[3]);
        $this->assertNull($targets[4]);
        // The empty trailing header is not a column to link
        $this->assertSame([4], $result->data['not_mapped']);
        $this->assertCount(5, $result->data['columns']);
    }

    public function test_the_mapping_names_the_duplicate_fields_labels_and_groups(): void
    {
        $result = (new ImportMappingTool())->handle($this->context, ['import_id' => 1]);

        $this->assertSame([['slug' => 'email', 'name' => 'E-mail']], $result->data['duplicates_detected_on']);
        $this->assertSame([['id' => 5, 'name' => 'Salon avril']], $result->data['labels_applied']);
        $this->assertSame([['id' => 3, 'name' => 'Equipe A']], $result->data['groups_applied']);
    }

    public function test_the_targets_are_the_ones_of_the_project_only(): void
    {
        $result = (new ImportMappingTool())->handle($this->context, ['import_id' => 1, 'view' => 'targets']);

        $this->assertSame(['first_name', 'email', 'ville'], array_column($result->data['fields'], 'slug'));
        $this->assertSame([7], array_column($result->data['categories'], 'id'));
        $this->assertSame([4], array_column($result->data['threads'], 'id'));
        $this->assertContains('events', array_column($result->data['special'], 'type'));
        $this->assertStringNotContainsString('Autre', json_encode($result->data));
    }

    public function test_an_import_of_another_project_is_not_found_by_either_tool(): void
    {
        $this->assertSame(ToolResult::NOT_FOUND, (new ImportMappingTool())->handle($this->context, ['import_id' => 3])->code);
        $this->assertSame(ToolResult::NOT_FOUND, $this->configure()->handle($this->context, ['import_id' => 3, 'mapping' => []])->code);
        $this->assertSame([], $this->sent);
    }

    public function test_the_columns_are_linked_through_the_routes_of_the_crm(): void
    {
        $result = $this->configure()->handle($this->context, ['import_id' => 1, 'mapping' => [
            ['column' => 4, 'target' => ['type' => 'field', 'slug' => 'email']],
            ['column' => 2, 'target' => ['type' => 'meta', 'slug' => 'ville']],
            ['column' => 3, 'target' => ['type' => 'category', 'id' => 7]],
            ['column' => 0, 'target' => ['type' => 'thread', 'id' => 4]],
            ['column' => 1, 'target' => ['type' => 'events']],
            ['column' => 5, 'target' => ['type' => 'none']],
        ]]);

        $this->assertFalse($result->isError);
        $this->assertSame(6, $result->data['applied']);
        $this->assertSame([
            ['PUT', 'import/1/mapping/4/field/email', []],
            ['PUT', 'import/1/mapping/2/meta/ville', []],
            ['PUT', 'import/1/mapping/3/category/7', []],
            ['PUT', 'import/1/mapping/0/thread/4', []],
            ['PUT', 'import/1/mapping/1/event', []],
            ['DELETE', 'import/1/mapping/5', []],
        ], $this->sent);
    }

    public function test_duplicates_labels_and_groups_are_set(): void
    {
        $result = $this->configure()->handle($this->context, [
            'import_id' => 1,
            'duplicates_fields' => ['email', 'meta->ville'],
            'add_labels' => [5],
            'remove_groups' => [3],
        ]);

        $this->assertFalse($result->isError);
        $this->assertSame([
            ['PUT', 'import/1', ['duplicates_fields' => [2, 3]]],
            ['PUT', 'import/1/label/5', []],
            ['DELETE', 'import/1/group/3', []],
        ], $this->sent);
    }

    public function test_one_wrong_item_changes_nothing(): void
    {
        $cases = [
            'colonne hors du fichier' => ['mapping' => [['column' => 1, 'target' => ['type' => 'events']], ['column' => 9, 'target' => ['type' => 'events']]]],
            'colonne donnée deux fois' => ['mapping' => [['column' => 1, 'target' => ['type' => 'events']], ['column' => 1, 'target' => ['type' => 'sms']]]],
            'champ inconnu' => ['mapping' => [['column' => 1, 'target' => ['type' => 'field', 'slug' => 'inconnu']]]],
            'champ d\'un autre projet' => ['mapping' => [['column' => 1, 'target' => ['type' => 'field', 'slug' => 'secret']]]],
            'champ perso pris pour un champ natif' => ['mapping' => [['column' => 1, 'target' => ['type' => 'field', 'slug' => 'ville']]]],
            'catégorie d\'un autre projet' => ['mapping' => [['column' => 1, 'target' => ['type' => 'category', 'id' => 8]]]],
            'doublons inconnus' => ['duplicates_fields' => ['inconnu']],
            'label d\'un autre projet' => ['add_labels' => [6]],
            'groupe d\'un autre projet' => ['add_groups' => [4]],
        ];

        foreach ($cases as $name => $input) {
            $result = $this->configure()->handle($this->context, ['import_id' => 1] + $input);

            $this->assertSame(ToolResult::VALIDATION_FAILED, $result->code, $name);
        }
        $this->assertSame([], $this->sent);
    }

    public function test_an_import_in_progress_or_an_empty_request_is_refused(): void
    {
        $this->assertSame(ToolResult::INVALID_INPUT, $this->configure()->handle($this->context, ['import_id' => 2, 'mapping' => [['column' => 0, 'target' => ['type' => 'sms']]]])->code);
        $this->assertSame(ToolResult::VALIDATION_FAILED, $this->configure()->handle($this->context, ['import_id' => 1])->code);
        $this->assertSame([], $this->sent);
    }

    public function test_a_partial_failure_is_told_and_a_total_one_is_an_error(): void
    {
        $this->failing = ['import/1/label/5'];

        $partial = $this->configure()->handle($this->context, ['import_id' => 1, 'add_labels' => [5], 'remove_groups' => [3]]);
        $this->assertFalse($partial->isError);
        $this->assertSame(1, $partial->data['applied']);
        $this->assertSame('ajouter le label 5', $partial->data['failed'][0]['what']);

        $this->failing = ['import/1/label/5'];
        $total = $this->configure()->handle($this->context, ['import_id' => 1, 'add_labels' => [5]]);
        $this->assertTrue($total->isError);
    }

    public function test_rights_and_risks(): void
    {
        $this->assertSame('prospectImport', (new ImportMappingTool())->ability());
        $this->assertSame(ImportMappingTool::READ, (new ImportMappingTool())->risk());
        $this->assertSame('prospectImport', $this->configure()->ability());
        $this->assertSame(ImportConfigureTool::WRITE, $this->configure()->risk());
    }
}
