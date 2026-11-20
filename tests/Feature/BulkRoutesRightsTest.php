<?php

namespace Tests\Feature;

use App\Http\Controllers\API\Project\Pipeline\LabelController as PipelineLabelController;
use App\Http\Controllers\API\Project\Prospect\FileController as ProspectFileController;
use App\Http\Controllers\API\Project\ProspectController;
use App\Http\Controllers\API\Project\TrashController;
use App\Http\Middleware\ProjectUserAccessCheck;
use App\Models\Calendar;
use App\Models\Category;
use App\Models\Document;
use App\Models\Folder;
use App\Models\Group;
use App\Models\Label;
use App\Models\Menu;
use App\Models\OrderStep;
use App\Models\Pipeline;
use App\Models\Product;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\Questionnaire;
use App\Models\Role;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\UsesInMemoryDatabase;
use Tests\TestCase;

/**
 * The routes that change many things at once (and the single routes next to them) now ask for the right the screen asks.
 * They used to check nothing: any member of the project could do them by calling the API.
 *
 * A call is "refused" when the right check stops it with 403 or 404. Anything else (a validation error because nothing
 * was sent, or a database error because the tables of the data are not there) means the check let it through.
 */
class BulkRoutesRightsTest extends TestCase
{
    use UsesInMemoryDatabase;

    protected const ROLE_CONTROLLERS = [
        'Calendar' => Calendar::class, 'Category' => Category::class, 'Document' => Document::class, 'Folder' => Folder::class,
        'Group' => Group::class, 'Label' => Label::class, 'Menu' => Menu::class, 'OrderStep' => OrderStep::class,
        'Product' => Product::class, 'Questionnaire' => Questionnaire::class, 'Thread' => Thread::class, 'User' => User::class,
    ];

    protected int $pid;
    protected int $otherPid;
    protected array $u = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpInMemoryDatabase();
        config(['logging.default' => 'null']);
        Event::fake([Authenticated::class]);

        foreach (['creator', 'bob', 'roleEditor', 'deleter', 'archiver'] as $name) {
            $this->u[$name] = $this->makeUser(ucfirst($name));
        }
        $this->pid = $this->makeProject('Alpha');
        $this->otherPid = $this->makeProject('Beta');
        DB::table('projects')->where('id', $this->pid)->update(['creator_id' => $this->u['creator']]);
        foreach ($this->u as $id) {
            DB::table('user_project')->insert(['user_id' => $id, 'project_id' => $this->pid]);
        }

        $this->give('roleEditor', ['all.project.role.update', 'all.project.pipeline.update', 'all.project.document.update']);
        $this->give('deleter', ['all.prospect.delete', 'all.project.category.delete']);
        $this->give('archiver', ['all.prospect.archive']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function give(string $who, array $permissions): void
    {
        $role = DB::table('roles')->insertGetId(['project_id' => $this->pid, 'name' => "Rôle $who", 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);
        foreach ($permissions as $permission) {
            $id = DB::table('permissions')->where('name', $permission)->value('id')
                ?? DB::table('permissions')->insertGetId(['name' => $permission, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);
            DB::table('role_has_permissions')->insert(['permission_id' => $id, 'role_id' => $role]);
        }
        DB::table('model_has_roles')->insert(['role_id' => $role, 'model_type' => User::class, 'model_id' => $this->u[$who], 'project_id' => $this->pid]);
    }

    /** Runs the call as this user in the project: 'refused' (403 or 404 from a check) or 'through' */
    protected function outcome(string $who, callable $call): string
    {
        $project = Project::withoutGlobalScopes()->find($this->pid);
        auth()->setUser(User::find($this->u[$who]));
        setPermissionsTeamId($this->pid);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        request()->merge(['project' => $project]);

        try {
            $call($project);

            return 'through';
        } catch (HttpException $e) {
            return in_array($e->getStatusCode(), [403, 404], true) ? 'refused' : 'through';
        } catch (ValidationException|\Throwable) {
            return 'through';
        }
    }

    protected function model(string $class, int $project): object
    {
        $model = new $class();
        $model->forceFill(['id' => 1, 'project_id' => $project]);

        return $model;
    }

    protected function role(int $project): Role
    {
        $role = new Role();
        $role->forceFill(['id' => 1, 'project_id' => $project]);

        return $role;
    }

    public function test_every_role_screen_route_is_refused_to_a_member_who_cannot_update_roles(): void
    {
        foreach (self::ROLE_CONTROLLERS as $name => $model) {
            $class = "App\\Http\\Controllers\\API\\Project\\Role\\{$name}Controller";
            $controller = new $class();

            $this->assertSame('refused', $this->outcome('bob', fn ($p) => $controller->bulk(Request::create('/', 'PUT', []), $p)), "$name bulk");
            $this->assertSame('refused', $this->outcome('bob', fn ($p) => $controller->update($p, $this->role($this->pid), $this->model($model, $this->pid))), "$name update");
            $this->assertSame('refused', $this->outcome('bob', fn ($p) => $controller->destroy($p, $this->role($this->pid), $this->model($model, $this->pid))), "$name destroy");
        }
    }

    public function test_who_may_update_roles_gets_through_but_never_to_a_role_of_another_project(): void
    {
        foreach (self::ROLE_CONTROLLERS as $name => $model) {
            $class = "App\\Http\\Controllers\\API\\Project\\Role\\{$name}Controller";
            $controller = new $class();

            $this->assertSame('through', $this->outcome('roleEditor', fn ($p) => $controller->bulk(Request::create('/', 'PUT', []), $p)), "$name bulk");
            $this->assertSame('through', $this->outcome('roleEditor', fn ($p) => $controller->update($p, $this->role($this->pid), $this->model($model, $this->pid))), "$name update");
            $this->assertSame('refused', $this->outcome('roleEditor', fn ($p) => $controller->update($p, $this->role($this->otherPid), $this->model($model, $this->pid))), "$name update, role of another project");
            $this->assertSame('refused', $this->outcome('roleEditor', fn ($p) => $controller->destroy($p, $this->role($this->otherPid), $this->model($model, $this->pid))), "$name destroy, role of another project");
        }
    }

    public function test_pipeline_stages_and_file_access_follow_the_right_of_their_screens(): void
    {
        $pipeline = new PipelineLabelController();
        $this->assertSame('refused', $this->outcome('bob', fn ($p) => $pipeline->bulk(Request::create('/', 'PUT', []), $p)));
        $this->assertSame('refused', $this->outcome('bob', fn ($p) => $pipeline->update($p, $this->model(Pipeline::class, $this->pid), $this->model(Label::class, $this->pid))));
        $this->assertSame('through', $this->outcome('roleEditor', fn ($p) => $pipeline->bulk(Request::create('/', 'PUT', []), $p)));

        $files = new ProspectFileController();
        $args = fn ($p) => [Request::create('/', 'PUT', []), $p, $this->model(Prospect::class, $this->pid), $this->model(Folder::class, $this->pid)];
        $this->assertSame('refused', $this->outcome('bob', fn ($p) => $files->bulk(...$args($p))));
        $this->assertSame('through', $this->outcome('roleEditor', fn ($p) => $files->bulk(...$args($p))));
    }

    public function test_the_actions_on_many_prospects_ask_the_right_of_each_action(): void
    {
        $controller = new ProspectController();
        $call = fn (string $action, array $extra = []) => fn ($p) => $controller->bulk(Request::create('/', 'PUT', ['prospects' => [1], 'action' => $action] + $extra), $p);

        foreach (['delete', 'force_delete', 'restore'] as $action) {
            $this->assertSame('refused', $this->outcome('bob', $call($action)), "$action without right");
            $this->assertSame('refused', $this->outcome('archiver', $call($action)), "$action with the right to archive only");
            $this->assertSame('through', $this->outcome('deleter', $call($action)), "$action with the right to delete");
        }
        foreach (['processed', 'not_processed'] as $action) {
            $this->assertSame('refused', $this->outcome('bob', $call($action)), "$action without right");
            $this->assertSame('refused', $this->outcome('deleter', $call($action)), "$action with the right to delete only");
            $this->assertSame('through', $this->outcome('archiver', $call($action)), "$action with the right to archive");
        }
        $this->assertSame('refused', $this->outcome('bob', $call('field', ['field' => 'city', 'value' => 'Lyon'])));
    }

    public function test_prospects_are_still_only_copied_to_a_project_the_user_belongs_to(): void
    {
        $controller = new ProspectController();
        $copy = fn ($p) => $controller->bulk(Request::create('/', 'PUT', ['prospects' => [1], 'action' => 'project', 'project' => $this->otherPid]), $p);

        // The creator holds every right here, but is not a member of the other project: the global scope of projects
        // (which was already there) hides it, so it cannot be found as a destination
        $this->assertSame('refused', $this->outcome('creator', $copy));

        DB::table('user_project')->insert(['user_id' => $this->u['creator'], 'project_id' => $this->otherPid]);
        $this->assertSame('through', $this->outcome('creator', $copy));
    }

    public function test_a_copied_prospect_is_visible_to_the_user_who_copied_it(): void
    {
        $this->give('bob', ['all.prospect.view']);
        DB::table('user_project')->insert(['user_id' => $this->u['bob'], 'project_id' => $this->otherPid]);

        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
        Schema::create('prospect_user', function (Blueprint $table) {
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('user_id');
        });
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
        });
        Schema::create('user_group', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('group_id');
        });
        Schema::create('prospect_group', function (Blueprint $table) {
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('group_id');
        });
        DB::table('prospects')->insert([
            'id' => 1,
            'project_id' => $this->pid,
            'creator_id' => $this->u['creator'],
            'email' => 'prospect@example.test',
        ]);
        $source = Project::withoutGlobalScopes()->findOrFail($this->pid);
        $target = Project::withoutGlobalScopes()->findOrFail($this->otherPid);
        auth()->setUser(User::findOrFail($this->u['bob']));
        $bulkRequest = Request::create('/api/project/alpha/prospect/bulk', 'PUT', [
            'prospects' => [999],
            'action' => 'project',
            'project' => $this->otherPid,
        ]);
        $route = new \Illuminate\Routing\Route('PUT', 'api/project/{project}/prospect/bulk', []);
        $route->bind($bulkRequest);
        $route->setParameter('project', $source);
        $bulkRequest->setRouteResolver(fn () => $route);
        $this->app->instance('request', $bulkRequest);
        setPermissionsTeamId(null);

        (new ProjectUserAccessCheck())->handle($bulkRequest, fn () => response()->noContent());
        $this->assertSame($this->pid, getPermissionsTeamId());
        $this->assertSame($this->otherPid, $bulkRequest->project);
        $this->assertSame($source, $bulkRequest->route('project'));
        $this->assertTrue(Prospect::whereKey(1)->exists());

        try {
            (new ProspectController())->bulk($bulkRequest, $source);
            $this->fail('A prospect outside the source project must not be reported as copied.');
        } catch (HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
        }

        $bulkRequest->merge(['prospects' => [1]]);
        Prospect::withoutEvents(function () use ($bulkRequest, $source) {
            (new ProspectController())->bulk($bulkRequest, $source);
        });

        setPermissionsTeamId($this->otherPid);

        $copy = $target->prospects()->where('email', 'prospect@example.test')->first();

        $this->assertNotNull($copy);
        $this->assertSame($this->u['bob'], $copy->creator_id);
        $this->assertNull($copy->deleted_at);
    }

    public function test_the_trash_asks_the_right_to_delete_each_kind_of_item(): void
    {
        $controller = new TrashController();
        $call = fn (array $items) => fn ($p) => $controller->bulk(Request::create('/', 'PUT', ['action' => 'restore'] + $items), $p);

        $this->assertSame('refused', $this->outcome('bob', $call(['prospects' => [1]])));
        $this->assertSame('refused', $this->outcome('bob', $call(['categories' => [1]])));
        // The right to delete prospects does not open the categories, and the other way round
        $this->assertSame('through', $this->outcome('deleter', $call(['prospects' => [1]])));
        $this->assertSame('through', $this->outcome('deleter', $call(['categories' => [1]])));
        $this->assertSame('refused', $this->outcome('deleter', $call(['calendars' => [1]])));
        $this->assertSame('refused', $this->outcome('archiver', $call(['prospects' => [1]])));
    }

    public function test_every_kind_of_item_of_the_trash_has_a_right(): void
    {
        $source = file_get_contents(base_path('app/Http/Controllers/API/Project/TrashController.php'));
        preg_match("/\\\$types = \\[(.*?)\\];/s", $source, $block);
        preg_match_all("/'([a-z-]+)'/", $block[1], $types);

        $this->assertEqualsCanonicalizing($types[1], array_keys(TrashController::RIGHTS), 'Un type de la corbeille sans droit (ou un droit sans type)');
    }
}
