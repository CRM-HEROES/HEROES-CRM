<?php

namespace Tests\Feature;

use App\Agents\AgentExecutionContext;
use App\Agents\Entities\ReadGateway;
use App\Agents\Tools\QuestionnaireBuildTool;
use App\Agents\Tools\QuestionnaireOverviewTool;
use App\Agents\Tools\QuestionnaireResponsesTool;
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
 * Questionnaires for the AI agents: reading how they are built, building one in a single request, reading the answers of a prospect.
 */
class QuestionnaireToolsTest extends TestCase
{
    use UsesInMemoryDatabase;

    protected int $pid;
    protected int $otherPid;
    protected array $u = [];
    protected array $sent = [];
    protected int $nextId = 100;
    protected ?string $failOn = null;
    protected bool $prospectVisible = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpInMemoryDatabase();
        config(['logging.default' => 'null']);
        Event::fake([Authenticated::class]);

        Schema::create('questionnaires', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('project_id');
            $t->string('name');
            $t->integer('order')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('questionnaire_sections', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('questionnaire_id');
            $t->string('name');
            $t->integer('order')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('questionnaire_questions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('section_id');
            $t->string('name');
            $t->string('type');
            $t->integer('order')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('questionnaire_options', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('question_id');
            $t->string('name');
            $t->unsignedBigInteger('redirection_id')->nullable();
            $t->integer('order')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('prospect_questionnaire_responses', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('prospect_id');
            $t->unsignedBigInteger('question_id');
            $t->unsignedBigInteger('option_id')->nullable();
            $t->text('response')->nullable();
            $t->integer('size')->nullable();
            $t->timestamps();
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
        DB::table('questionnaires')->insert([['id' => 1, 'project_id' => $this->pid, 'name' => 'Visite', 'created_at' => $now, 'updated_at' => $now], ['id' => 2, 'project_id' => $this->otherPid, 'name' => 'Autre', 'created_at' => $now, 'updated_at' => $now]]);
        DB::table('questionnaire_sections')->insert([['id' => 1, 'questionnaire_id' => 1, 'name' => 'Logement', 'order' => 1, 'created_at' => $now, 'updated_at' => $now]]);
        DB::table('questionnaire_questions')->insert([
            ['id' => 1, 'section_id' => 1, 'name' => 'Type de logement', 'type' => 'radio', 'order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'section_id' => 1, 'name' => 'Remarques', 'type' => 'textarea', 'order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'section_id' => 1, 'name' => 'Photo du toit', 'type' => 'image', 'order' => 3, 'created_at' => $now, 'updated_at' => $now],
        ]);
        DB::table('questionnaire_options')->insert([['id' => 1, 'question_id' => 1, 'name' => 'Maison', 'order' => 1, 'created_at' => $now, 'updated_at' => $now], ['id' => 2, 'question_id' => 1, 'name' => 'Appartement', 'order' => 2, 'created_at' => $now, 'updated_at' => $now]]);
        DB::table('prospect_questionnaire_responses')->insert([
            ['prospect_id' => 7, 'question_id' => 1, 'option_id' => 2, 'response' => null, 'created_at' => $now, 'updated_at' => $now],
            ['prospect_id' => 7, 'question_id' => 2, 'option_id' => null, 'response' => '<b>Toit à refaire</b>', 'created_at' => $now, 'updated_at' => $now],
            ['prospect_id' => 7, 'question_id' => 3, 'option_id' => null, 'response' => 'pr/secret/toit.jpg', 'created_at' => $now, 'updated_at' => $now],
            ['prospect_id' => 8, 'question_id' => 2, 'option_id' => null, 'response' => 'Autre prospect', 'created_at' => $now, 'updated_at' => $now],
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

    protected function builder(): QuestionnaireBuildTool
    {
        $api = Mockery::mock(InternalApi::class);
        $api->shouldReceive('call')->andReturnUsing(function ($context, $method, $path, $data = []) {
            $this->sent[] = [$method, $path, $data];

            if ($this->failOn && str_contains($path, $this->failOn)) {
                return new InternalResponse(422, ['message' => 'refusé', 'errors' => ['name' => ['Nom invalide']]], '{}');
            }

            return new InternalResponse(200, ['id' => $this->nextId++], '{}');
        });

        return new QuestionnaireBuildTool($api);
    }

    protected function responses(): QuestionnaireResponsesTool
    {
        $reader = Mockery::mock(ReadGateway::class);
        $reader->shouldReceive('visibleProspectIds')->andReturnUsing(fn ($context, array $ids) => ['ids' => $this->prospectVisible ? $ids : []]);

        return new QuestionnaireResponsesTool($reader);
    }

    protected function structure(): array
    {
        return ['name' => 'Satisfaction', 'sections' => [
            ['name' => 'Accueil', 'questions' => [
                ['name' => 'Note', 'type' => 'radio', 'choices' => ['Bien', 'Moyen']],
                ['name' => 'Commentaire', 'type' => 'text'],
            ]],
            ['name' => 'Suite', 'questions' => [['name' => 'Rappel souhaité ?', 'type' => 'checkbox', 'choices' => ['Oui', 'Non']]]],
        ]];
    }

    // Reading

    public function test_the_questionnaires_of_the_project_are_listed_with_their_size(): void
    {
        $result = (new QuestionnaireOverviewTool())->handle($this->ctx('creator'), []);

        $this->assertSame([['id' => 1, 'name' => 'Visite', 'sections' => 1]], $result->data['questionnaires']);
    }

    public function test_a_questionnaire_is_given_section_by_section_with_its_choices(): void
    {
        $result = (new QuestionnaireOverviewTool())->handle($this->ctx('creator'), ['questionnaire_id' => 1]);

        $questions = $result->data['sections'][0]['questions'];
        $this->assertSame(['Type de logement', 'Remarques', 'Photo du toit'], array_column($questions, 'name'));
        $this->assertSame(['Maison', 'Appartement'], array_column($questions[0]['choices'], 'name'));
        $this->assertSame('image', $questions[2]['type']);
        $this->assertSame(ToolResult::NOT_FOUND, (new QuestionnaireOverviewTool())->handle($this->ctx('creator'), ['questionnaire_id' => 2])->code);
    }

    public function test_the_answers_of_a_prospect_are_given_cut_and_a_file_is_never_given(): void
    {
        $result = $this->responses()->handle($this->ctx('creator'), ['prospect_id' => 7, 'questionnaire_id' => 1]);

        $this->assertFalse($result->isError);
        $answers = array_column($result->data['answers'], 'answer', 'question');
        $this->assertSame('Appartement', $answers['Type de logement']);
        $this->assertSame('Toit à refaire', $answers['Remarques']);
        $this->assertSame('[fichier joint]', $answers['Photo du toit']);
        $this->assertStringNotContainsString('secret', json_encode($result->data));
        $this->assertStringNotContainsString('Autre prospect', json_encode($result->data));
    }

    public function test_the_answers_need_a_visible_prospect_and_a_questionnaire_of_the_project(): void
    {
        $this->assertSame(ToolResult::NOT_FOUND, $this->responses()->handle($this->ctx('creator'), ['prospect_id' => 7, 'questionnaire_id' => 2])->code);

        $this->prospectVisible = false;
        $this->assertSame(ToolResult::NOT_FOUND, $this->responses()->handle($this->ctx('creator'), ['prospect_id' => 7, 'questionnaire_id' => 1])->code);
    }

    // Building

    public function test_a_questionnaire_is_built_piece_by_piece_through_the_routes_of_the_crm(): void
    {
        $result = $this->builder()->handle($this->ctx('creator'), $this->structure());

        $this->assertFalse($result->isError);
        $this->assertSame(['done' => true, 'questionnaire_id' => 100, 'created_questionnaire' => true, 'sections' => 2, 'questions' => 3, 'choices' => 4], $result->data);
        $this->assertSame([
            ['POST', 'questionnaire', ['name' => 'Satisfaction']],
            ['POST', 'questionnaire/100/section', ['name' => 'Accueil']],
            ['POST', 'questionnaire/100/section/101/question', ['name' => 'Note', 'type' => 'radio']],
            ['POST', 'questionnaire/100/section/101/question/102/option', ['name' => 'Bien']],
            ['POST', 'questionnaire/100/section/101/question/102/option', ['name' => 'Moyen']],
            ['POST', 'questionnaire/100/section/101/question', ['name' => 'Commentaire', 'type' => 'text']],
            ['POST', 'questionnaire/100/section', ['name' => 'Suite']],
            ['POST', 'questionnaire/100/section/106/question', ['name' => 'Rappel souhaité ?', 'type' => 'checkbox']],
            ['POST', 'questionnaire/100/section/106/question/107/option', ['name' => 'Oui']],
            ['POST', 'questionnaire/100/section/106/question/107/option', ['name' => 'Non']],
        ], $this->sent);
    }

    public function test_sections_are_added_to_an_existing_questionnaire_without_creating_another(): void
    {
        $result = $this->builder()->handle($this->ctx('creator'), ['questionnaire_id' => 1, 'sections' => [['name' => 'Suite', 'questions' => [['name' => 'Date', 'type' => 'date']]]]]);

        $this->assertFalse($result->isError);
        $this->assertFalse($result->data['created_questionnaire']);
        $this->assertSame(['POST', 'questionnaire/1/section', ['name' => 'Suite']], $this->sent[0]);
        $this->assertSame(ToolResult::NOT_FOUND, $this->builder()->handle($this->ctx('creator'), ['questionnaire_id' => 2, 'sections' => [['name' => 'X', 'questions' => []]]])->code);
    }

    public function test_a_wrong_structure_changes_nothing(): void
    {
        $base = ['name' => 'Q'];
        $cases = [
            'sans nom' => ['sections' => [['name' => 'S', 'questions' => []]]],
            'section sans nom' => $base + ['sections' => [['name' => ' ', 'questions' => []]]],
            'type inconnu' => $base + ['sections' => [['name' => 'S', 'questions' => [['name' => 'Q', 'type' => 'pigeon']]]]],
            'radio sans choix' => $base + ['sections' => [['name' => 'S', 'questions' => [['name' => 'Q', 'type' => 'radio', 'choices' => ['Un seul']]]]]],
            'choix sur un texte' => $base + ['sections' => [['name' => 'S', 'questions' => [['name' => 'Q', 'type' => 'text', 'choices' => ['A', 'B']]]]]],
            'aucune section' => $base + ['sections' => []],
        ];

        foreach ($cases as $name => $input) {
            $this->assertSame(ToolResult::VALIDATION_FAILED, $this->builder()->handle($this->ctx('creator'), $input)->code, $name);
        }
        $this->assertSame([], $this->sent);
    }

    public function test_the_size_is_limited(): void
    {
        $questions = array_fill(0, 20, ['name' => 'Q', 'type' => 'text']);
        $tooMany = ['name' => 'Q', 'sections' => [['name' => 'A', 'questions' => $questions], ['name' => 'B', 'questions' => $questions], ['name' => 'C', 'questions' => [['name' => 'Q', 'type' => 'text']]]]];

        $this->assertSame(ToolResult::LIMIT_EXCEEDED, $this->builder()->handle($this->ctx('creator'), $tooMany)->code);
        $this->assertSame([], $this->sent);
    }

    public function test_the_right_of_the_screen_is_asked_because_the_routes_ask_none(): void
    {
        $this->assertSame(ToolResult::PERMISSION_DENIED, $this->builder()->handle($this->ctx('bob'), $this->structure())->code);
        $this->assertSame(ToolResult::PERMISSION_DENIED, $this->builder()->handle($this->ctx('bob'), ['questionnaire_id' => 1] + $this->structure())->code);
        $this->assertSame([], $this->sent);
    }

    public function test_what_was_made_before_a_refusal_is_told(): void
    {
        $this->failOn = '/question';

        $result = $this->builder()->handle($this->ctx('creator'), $this->structure());

        $this->assertTrue($result->isError);
        $this->assertStringContainsString('questionnaire 100', $result->data['error']);
        $this->assertStringContainsString('1 section(s)', $result->data['error']);
        $this->assertStringContainsString('Corrige ou supprime', $result->data['error']);
    }

    public function test_the_checks_come_before_the_human_is_asked(): void
    {
        $this->assertNull($this->builder()->precheck($this->ctx('creator'), $this->structure()));
        $this->assertSame(ToolResult::VALIDATION_FAILED, $this->builder()->precheck($this->ctx('creator'), ['name' => 'Q', 'sections' => [['name' => 'S', 'questions' => [['name' => 'Q', 'type' => 'radio']]]]])->code);
        $this->assertSame(ToolResult::PERMISSION_DENIED, $this->builder()->precheck($this->ctx('bob'), $this->structure())->code);
        $this->assertSame([], $this->sent);
    }

    public function test_risks_and_schema(): void
    {
        $this->assertSame(QuestionnaireBuildTool::WRITE, $this->builder()->risk());
        $this->assertSame(QuestionnaireOverviewTool::READ, (new QuestionnaireOverviewTool())->risk());
        $this->assertSame(QuestionnaireResponsesTool::READ, $this->responses()->risk());
        $this->assertSame(QuestionnaireBuildTool::TYPES, $this->builder()->inputSchema()['properties']['sections']['items']['properties']['questions']['items']['properties']['type']['enum']);
    }
}
