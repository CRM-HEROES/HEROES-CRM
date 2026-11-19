<?php

namespace Tests\Feature;

use App\Http\Controllers\API\Project\UserController;
use App\Models\Project;
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
 * Taking users out of the project in one go (PUT user/bulk). Before, the route checked nothing: any member of the project
 * could take every other member out. It now asks what removing one user asks: a super administrator or an administrator.
 */
class UserBulkRouteTest extends TestCase
{
    use UsesInMemoryDatabase;

    protected int $pid;
    protected array $u = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpInMemoryDatabase();
        config(['logging.default' => 'null']);
        Event::fake([Authenticated::class]);

        Schema::table('user_project', function (Blueprint $table) {
            foreach (['relevance_event', 'relevance_prospect', 'relevance_order_action', 'relevance_message', 'prospects_count'] as $column) {
                $table->integer($column)->nullable();
            }
        });

        foreach (['creator' => null, 'admin' => null, 'bob' => null, 'carol' => null, 'dave' => null, 'root' => 'super_admin'] as $name => $system) {
            $this->u[$name] = $this->makeUser(ucfirst($name), null, $system);
        }
        $this->pid = $this->makeProject('Alpha');
        DB::table('projects')->where('id', $this->pid)->update(['creator_id' => $this->u['creator']]);
        foreach (['creator', 'admin', 'bob', 'carol', 'dave'] as $name) {
            DB::table('user_project')->insert(['user_id' => $this->u[$name], 'project_id' => $this->pid]);
        }

        // The administrator holds every permission of the project through a role
        $role = DB::table('roles')->insertGetId(['project_id' => $this->pid, 'name' => 'Admin', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);
        $permission = DB::table('permissions')->insertGetId(['name' => 'all', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('role_has_permissions')->insert(['permission_id' => $permission, 'role_id' => $role]);
        DB::table('model_has_roles')->insert(['role_id' => $role, 'model_type' => User::class, 'model_id' => $this->u['admin'], 'project_id' => $this->pid]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        setPermissionsTeamId($this->pid);
    }

    /** Calls the route as this user; returns the HTTP status */
    protected function bulk(string $who, array $input): int
    {
        $project = Project::withoutGlobalScopes()->find($this->pid);
        auth()->setUser(User::find($this->u[$who]));
        setPermissionsTeamId($this->pid);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        request()->merge(['project' => $project]);

        try {
            $response = (new UserController())->bulk(Request::create('/', 'PUT', $input), $project);

            return is_array($response) ? 200 : $response->getStatusCode();
        } catch (HttpException $e) {
            return $e->getStatusCode();
        } catch (ValidationException) {
            return 422;
        }
    }

    protected function members(): array
    {
        return DB::table('user_project')->where('project_id', $this->pid)->pluck('user_id')->map(fn ($id) => (int) $id)->sort()->values()->all();
    }

    protected function ids(string ...$names): array
    {
        return array_map(fn ($name) => $this->u[$name], $names);
    }

    public function test_a_member_without_administration_cannot_take_anyone_out(): void
    {
        $before = $this->members();

        $this->assertSame(404, $this->bulk('bob', ['action' => 'delete', 'users' => $this->ids('carol', 'dave')]));
        $this->assertSame(404, $this->bulk('bob', ['action' => 'delete', 'users' => $this->ids('creator', 'admin', 'bob')]));
        $this->assertSame($before, $this->members());
    }

    public function test_an_administrator_takes_users_out_of_the_project(): void
    {
        $this->assertSame(200, $this->bulk('admin', ['action' => 'delete', 'users' => $this->ids('carol', 'dave')]));

        $this->assertSame($this->ids('creator', 'admin', 'bob'), $this->members());
    }

    public function test_the_creator_and_a_super_administrator_may_do_it_too(): void
    {
        $this->assertSame(200, $this->bulk('creator', ['action' => 'delete', 'users' => $this->ids('carol')]));
        $this->assertSame(200, $this->bulk('root', ['action' => 'delete', 'users' => $this->ids('dave')]));

        $this->assertSame($this->ids('creator', 'admin', 'bob'), $this->members());
    }

    public function test_only_the_members_of_the_project_are_concerned(): void
    {
        // root is not a member of the project: asking for him changes nothing and does not fail
        $this->assertSame(200, $this->bulk('admin', ['action' => 'delete', 'users' => $this->ids('root')]));

        $this->assertSame($this->ids('creator', 'admin', 'bob', 'carol', 'dave'), $this->members());
    }

    public function test_an_unknown_action_and_a_list_that_is_not_a_list_are_refused(): void
    {
        $this->assertSame(400, $this->bulk('admin', ['action' => 'archive', 'users' => $this->ids('bob')]));
        $this->assertSame(422, $this->bulk('admin', ['action' => 'delete', 'users' => 'bob']));
        $this->assertSame(422, $this->bulk('admin', ['action' => 'delete']));
        $this->assertCount(5, $this->members());
    }
}
