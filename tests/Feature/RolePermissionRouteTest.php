<?php

namespace Tests\Feature;

use App\Http\Controllers\API\Project\Role\PermissionController;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\UsesInMemoryDatabase;
use Tests\TestCase;

/**
 * The permissions of a role: who may see and change them. Before, the route checked nothing: any member of the project
 * could give any permission (even a new one) to any role.
 */
class RolePermissionRouteTest extends TestCase
{
    use UsesInMemoryDatabase;

    protected int $pid;
    protected int $otherPid;
    protected int $creator;
    protected int $editor;
    protected int $bare;
    protected int $team;
    protected int $targetRole;
    protected int $foreignRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpInMemoryDatabase();
        config(['logging.default' => 'null']);
        // Signing a user in fires an event that records the device of the session: not needed here
        Event::fake([Authenticated::class]);

        $this->creator = $this->makeUser('Creator');
        $this->editor = $this->makeUser('Editor');
        $this->bare = $this->makeUser('Bare');
        $this->pid = $this->makeProject('Alpha');
        $this->otherPid = $this->makeProject('Beta');
        DB::table('projects')->where('id', $this->pid)->update(['creator_id' => $this->creator]);
        foreach ([$this->creator, $this->editor, $this->bare] as $user) {
            DB::table('user_project')->insert(['user_id' => $user, 'project_id' => $this->pid]);
        }

        // The editor may update roles and see prospects, nothing more
        $this->role($this->pid, 'Gestionnaire', ['all.project.role.update', 'all.prospect.view'], $this->editor);
        $this->targetRole = $this->role($this->pid, 'Télépro', []);
        $this->foreignRole = $this->role($this->otherPid, 'Autre projet', []);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        setPermissionsTeamId($this->pid);
    }

    protected function role(int $project, string $name, array $permissions, ?int $user = null): int
    {
        $role = DB::table('roles')->insertGetId(['project_id' => $project, 'name' => $name, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);

        foreach ($permissions as $permission) {
            $id = DB::table('permissions')->where('name', $permission)->value('id')
                ?? DB::table('permissions')->insertGetId(['name' => $permission, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);
            DB::table('role_has_permissions')->insert(['permission_id' => $id, 'role_id' => $role]);
        }
        if ($user) {
            DB::table('model_has_roles')->insert(['role_id' => $role, 'model_type' => User::class, 'model_id' => $user, 'project_id' => $project]);
        }

        return $role;
    }

    /** Calls the controller as this user, in the project; returns the HTTP status */
    protected function asUser(int $userId, string $action, int $roleId, string $permission = '', ?int $projectId = null): int
    {
        $projectId ??= $this->pid;
        $project = Project::withoutGlobalScopes()->find($projectId);
        auth()->setUser(User::find($userId));
        setPermissionsTeamId($projectId);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        // What the project middleware gives the policies
        request()->merge(['project' => $project]);

        try {
            $controller = new PermissionController();
            $role = Role::find($roleId);
            $action === 'index' ? $controller->index($project, $role) : $controller->$action($project, $role, $permission);

            return 200;
        } catch (HttpException $e) {
            return $e->getStatusCode();
        }
    }

    protected function has(int $roleId, string $permission): bool
    {
        return DB::table('role_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('role_id', $roleId)->where('permissions.name', $permission)->exists();
    }

    public function test_a_member_without_the_right_to_update_roles_can_neither_see_nor_change_permissions(): void
    {
        $this->assertSame(404, $this->asUser($this->bare, 'index', $this->targetRole));
        $this->assertSame(404, $this->asUser($this->bare, 'update', $this->targetRole, 'all'));
        $this->assertSame(404, $this->asUser($this->bare, 'destroy', $this->targetRole, 'all.prospect.view'));
        $this->assertFalse($this->has($this->targetRole, 'all'));
    }

    public function test_a_role_of_another_project_is_never_reached(): void
    {
        $this->assertSame(404, $this->asUser($this->creator, 'index', $this->foreignRole));
        $this->assertSame(404, $this->asUser($this->creator, 'update', $this->foreignRole, 'all.prospect.view'));
        $this->assertSame(404, $this->asUser($this->creator, 'destroy', $this->foreignRole, 'all.prospect.view'));
        $this->assertFalse($this->has($this->foreignRole, 'all.prospect.view'));
    }

    public function test_the_creator_of_the_project_can_give_and_take_any_permission(): void
    {
        $this->assertSame(200, $this->asUser($this->creator, 'update', $this->targetRole, 'all.prospect.delete'));
        $this->assertTrue($this->has($this->targetRole, 'all.prospect.delete'));

        $this->assertSame(200, $this->asUser($this->creator, 'destroy', $this->targetRole, 'all.prospect.delete'));
        $this->assertFalse($this->has($this->targetRole, 'all.prospect.delete'));
    }

    public function test_an_editor_of_roles_can_only_give_what_he_holds_himself(): void
    {
        $this->assertSame(200, $this->asUser($this->editor, 'update', $this->targetRole, 'all.prospect.view'));
        $this->assertTrue($this->has($this->targetRole, 'all.prospect.view'));

        // Not held: refused, and nothing is created
        $this->assertSame(403, $this->asUser($this->editor, 'update', $this->targetRole, 'all.prospect.delete'));
        $this->assertSame(403, $this->asUser($this->editor, 'update', $this->targetRole, 'all'));
        $this->assertFalse($this->has($this->targetRole, 'all'));
        $this->assertFalse(DB::table('permissions')->where('name', 'all')->exists());
    }

    public function test_an_editor_of_roles_may_take_a_permission_away(): void
    {
        $this->asUser($this->creator, 'update', $this->targetRole, 'all.prospect.view');

        $this->assertSame(200, $this->asUser($this->editor, 'destroy', $this->targetRole, 'all.prospect.view'));
        $this->assertFalse($this->has($this->targetRole, 'all.prospect.view'));
    }

    public function test_a_permission_name_that_is_not_a_path_of_keys_is_refused(): void
    {
        foreach (['admin', 'All.Project', 'all..x', 'all.x y', 'all.<script>', 'prospect.view'] as $name) {
            $this->assertSame(422, $this->asUser($this->creator, 'update', $this->targetRole, $name), $name);
            $this->assertSame(422, $this->asUser($this->creator, 'destroy', $this->targetRole, $name), $name);
        }
        $this->assertFalse(DB::table('permissions')->where('name', 'admin')->exists());
    }

    public function test_taking_away_a_permission_that_does_not_exist_is_not_an_error(): void
    {
        $this->assertSame(200, $this->asUser($this->creator, 'destroy', $this->targetRole, 'all.never.created'));
    }

    public function test_the_permissions_of_a_role_can_be_read_by_who_may_update_it(): void
    {
        $this->assertSame(200, $this->asUser($this->editor, 'index', $this->targetRole));
    }
}
