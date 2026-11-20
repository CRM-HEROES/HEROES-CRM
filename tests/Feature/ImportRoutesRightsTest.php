<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\UsesInMemoryDatabase;
use Tests\TestCase;

/**
 * The routes of the imports (file, mapping, duplicates, labels, groups, roles, users) ask for the right of the import
 * screen. They used to check nothing: any member could change or delete the imports (and their prospects) by calling the API.
 */
class ImportRoutesRightsTest extends TestCase
{
    use UsesInMemoryDatabase;

    /** controller => the methods that change or reveal something */
    protected const ROUTES = [
        'ImportController' => ['store', 'download', 'update', 'syncScript', 'destroy'],
        'Import\\DuplicateController' => ['find', 'show', 'destroy'],
        'Import\\GroupController' => ['update', 'destroy'],
        'Import\\LabelController' => ['update', 'destroy'],
        'Import\\RoleController' => ['update', 'destroy'],
        'Import\\UserController' => ['update', 'destroy'],
        'Import\\UserGroupController' => ['update', 'destroy'],
        'Import\\MappingController' => ['field', 'meta', 'category', 'thread', 'event', 'order', 'sms', 'interaction', 'link', 'user', 'destroy'],
    ];

    protected int $pid;
    protected array $u = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpInMemoryDatabase();
        config(['logging.default' => 'null']);
        Event::fake([Authenticated::class]);

        foreach (['creator', 'bob', 'importer'] as $name) {
            $this->u[$name] = $this->makeUser(ucfirst($name));
        }
        $this->pid = $this->makeProject('Alpha');
        DB::table('projects')->where('id', $this->pid)->update(['creator_id' => $this->u['creator']]);
        foreach ($this->u as $id) {
            DB::table('user_project')->insert(['user_id' => $id, 'project_id' => $this->pid]);
        }

        $role = DB::table('roles')->insertGetId(['project_id' => $this->pid, 'name' => 'Importeur', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);
        $permission = DB::table('permissions')->insertGetId(['name' => 'all.prospect.import', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('role_has_permissions')->insert(['permission_id' => $permission, 'role_id' => $role]);
        DB::table('model_has_roles')->insert(['role_id' => $role, 'model_type' => User::class, 'model_id' => $this->u['importer'], 'project_id' => $this->pid]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** Calls the method as this user, whatever it needs: 'refused' (403/404) or 'through' */
    protected function outcome(string $who, string $controller, string $method): string
    {
        $project = Project::withoutGlobalScopes()->find($this->pid);
        auth()->setUser(User::find($this->u[$who]));
        setPermissionsTeamId($this->pid);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        request()->merge(['project' => $project]);

        $class = "App\\Http\\Controllers\\API\\Project\\$controller";
        $reflection = new \ReflectionMethod($class, $method);
        $args = [];
        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType()?->getName();
            $args[] = match (true) {
                $type === Request::class => Request::create('/', 'PUT', []),
                $type === Project::class => $project,
                $type !== null && is_subclass_of($type, \Illuminate\Database\Eloquent\Model::class) => (new $type())->forceFill(['id' => 1, 'project_id' => $this->pid]),
                $type !== null && class_exists($type) => app($type),
                default => 0,
            };
        }

        try {
            (new $class())->$method(...$args);

            return 'through';
        } catch (HttpException $e) {
            return in_array($e->getStatusCode(), [403, 404], true) ? 'refused' : 'through';
        } catch (\Throwable) {
            // A validation error, or the data tables that are not here: the check let it through
            return 'through';
        }
    }

    public function test_a_member_without_the_right_to_import_is_refused_everywhere(): void
    {
        foreach (self::ROUTES as $controller => $methods) {
            foreach ($methods as $method) {
                $this->assertSame('refused', $this->outcome('bob', $controller, $method), "$controller::$method");
            }
        }
    }

    public function test_who_may_import_gets_through_everywhere(): void
    {
        foreach (self::ROUTES as $controller => $methods) {
            foreach ($methods as $method) {
                $this->assertSame('through', $this->outcome('importer', $controller, $method), "$controller::$method");
            }
        }
    }

    public function test_the_creator_of_the_project_keeps_every_right(): void
    {
        foreach (self::ROUTES as $controller => $methods) {
            foreach ($methods as $method) {
                $this->assertSame('through', $this->outcome('creator', $controller, $method), "$controller::$method");
            }
        }
    }
}
