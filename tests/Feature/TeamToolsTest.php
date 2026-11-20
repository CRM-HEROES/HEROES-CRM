<?php

namespace Tests\Feature;

use App\Agents\AgentExecutionContext;
use App\Agents\Tools\ProjectUserMembershipTool;
use App\Agents\Tools\TeamAccess;
use App\Agents\Tools\TeamMembersTool;
use App\Agents\Tools\ToolResult;
use App\Agents\Tools\UserRolesSetTool;
use App\Agents\Writes\InternalApi;
use App\Agents\Writes\InternalResponse;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\UsesInMemoryDatabase;
use Tests\TestCase;

/**
 * The team of the project for the AI agents: reading it (A), and giving access and roles, always by a human (B).
 */
class TeamToolsTest extends TestCase
{
    use UsesInMemoryDatabase;

    protected int $pid;
    protected array $u = [];
    protected array $role = [];
    protected array $sent = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpInMemoryDatabase();
        config(['logging.default' => 'null']);
        Event::fake([Authenticated::class]);

        // What the relation Project::users and the visibility scope of users read
        Schema::table('user_project', function (Blueprint $table) {
            foreach (['relevance_event', 'relevance_prospect', 'relevance_order_action', 'relevance_message', 'prospects_count'] as $column) {
                $table->integer($column)->nullable();
            }
        });
        Schema::create('user_assignable_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('assignable_user_id');
            $table->unsignedBigInteger('project_id')->nullable();
        });

        foreach (['creator' => null, 'admin' => null, 'manager' => null, 'bob' => null, 'carol' => null, 'root' => 'super_admin', 'outsider' => null] as $name => $systemRole) {
            $this->u[$name] = $this->makeUser(ucfirst($name), null, $systemRole);
        }
        $this->pid = $this->makeProject('Alpha');
        DB::table('projects')->where('id', $this->pid)->update(['creator_id' => $this->u['creator']]);
        foreach (['creator', 'admin', 'manager', 'bob', 'carol', 'root'] as $name) {
            DB::table('user_project')->insert(['user_id' => $this->u[$name], 'project_id' => $this->pid]);
        }

        $this->role['admin'] = $this->makeRole('Admin', ['all'], 'admin');
        $this->role['manager'] = $this->makeRole('Manager', ['all.user.role', 'all.prospect.view'], 'manager');
        $this->role['telepro'] = $this->makeRole('Télépro', ['all.prospect.view'], 'bob');
        $this->role['superviseur'] = $this->makeRole('Superviseur', ['all.prospect.view', 'all.prospect.delete']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        setPermissionsTeamId($this->pid);
    }

    protected function makeRole(string $name, array $permissions, ?string $user = null): int
    {
        $role = DB::table('roles')->insertGetId(['project_id' => $this->pid, 'name' => $name, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);
        foreach ($permissions as $permission) {
            $id = DB::table('permissions')->where('name', $permission)->value('id')
                ?? DB::table('permissions')->insertGetId(['name' => $permission, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);
            DB::table('role_has_permissions')->insert(['permission_id' => $id, 'role_id' => $role]);
        }
        if ($user) {
            DB::table('model_has_roles')->insert(['role_id' => $role, 'model_type' => User::class, 'model_id' => $this->u[$user], 'project_id' => $this->pid]);
        }

        return $role;
    }

    protected function as(string $who): AgentExecutionContext
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

            return new InternalResponse(200, ['message' => 'ok'], '{}');
        });

        return $api;
    }

    protected function roles(): UserRolesSetTool
    {
        return new UserRolesSetTool($this->api(), new TeamAccess());
    }

    protected function membership(): ProjectUserMembershipTool
    {
        return new ProjectUserMembershipTool($this->api(), new TeamAccess());
    }

    // A — reading

    public function test_the_team_is_listed_with_roles_and_without_contact_details(): void
    {
        $result = (new TeamMembersTool())->handle($this->as('admin'), ['view' => 'users']);

        $this->assertFalse($result->isError);
        $byName = collect($result->data['users'])->keyBy('name');
        $this->assertSame(['Télépro'], array_column($byName['Bob']['roles'], 'name'));
        $this->assertTrue($byName['Creator']['is_project_creator']);
        $this->assertStringNotContainsString('@', json_encode($result->data));
        // A member of another project is not there
        $this->assertArrayNotHasKey('Outsider', $byName->all());
    }

    public function test_the_list_can_be_searched_by_name(): void
    {
        $result = (new TeamMembersTool())->handle($this->as('admin'), ['view' => 'users', 'search' => 'car']);

        $this->assertSame(['Carol'], array_column($result->data['users'], 'name'));
    }

    public function test_the_roles_need_the_right_to_manage_the_roles_of_users(): void
    {
        $denied = (new TeamMembersTool())->handle($this->as('bob'), ['view' => 'roles']);
        $this->assertSame(ToolResult::PERMISSION_DENIED, $denied->code);

        $roles = (new TeamMembersTool())->handle($this->as('manager'), ['view' => 'roles']);
        $this->assertFalse($roles->isError);
        $this->assertContains('Télépro', array_column($roles->data['roles'], 'name'));
    }

    public function test_the_permissions_of_a_role_need_the_right_to_update_roles(): void
    {
        $manager = (new TeamMembersTool())->handle($this->as('manager'), ['view' => 'roles', 'role_id' => $this->role['telepro']]);
        $this->assertNull($manager->data['permissions']);
        $this->assertSame(['Bob'], array_column($manager->data['members'], 'name'));

        $admin = (new TeamMembersTool())->handle($this->as('admin'), ['view' => 'roles', 'role_id' => $this->role['superviseur']]);
        $this->assertEqualsCanonicalizing(['all.prospect.view', 'all.prospect.delete'], $admin->data['permissions']);
    }

    // B — roles

    public function test_an_administrator_gives_and_takes_roles_through_the_routes_of_the_crm(): void
    {
        $ctx = $this->as('admin');

        $this->assertFalse($this->roles()->handle($ctx, ['action' => 'assign', 'user_ids' => [$this->u['carol']], 'role_ids' => [$this->role['telepro'], $this->role['superviseur']]])->isError);
        $this->assertFalse($this->roles()->handle($ctx, ['action' => 'remove', 'user_ids' => [$this->u['bob']], 'role_ids' => [$this->role['telepro']]])->isError);

        $c = $this->u['carol'];
        $this->assertSame([
            ['PUT', "user/$c/role/{$this->role['telepro']}", []],
            ['PUT', "user/$c/role/{$this->role['superviseur']}", []],
            ['DELETE', "user/{$this->u['bob']}/role/{$this->role['telepro']}", []],
        ], $this->sent);
    }

    public function test_someone_who_manages_roles_only_gives_roles_whose_permissions_he_holds(): void
    {
        $ctx = $this->as('manager');

        $ok = $this->roles()->handle($ctx, ['action' => 'assign', 'user_ids' => [$this->u['carol']], 'role_ids' => [$this->role['telepro']]]);
        $this->assertFalse($ok->isError);

        $this->sent = [];
        $beyond = $this->roles()->handle($ctx, ['action' => 'assign', 'user_ids' => [$this->u['carol']], 'role_ids' => [$this->role['superviseur']]]);
        $this->assertSame(ToolResult::PERMISSION_DENIED, $beyond->code);
        $this->assertStringContainsString('all.prospect.delete', $beyond->data['error']);
        $this->assertSame([], $this->sent);

        // Taking a role away is always possible for who may manage roles
        $this->assertFalse($this->roles()->handle($ctx, ['action' => 'remove', 'user_ids' => [$this->u['bob']], 'role_ids' => [$this->role['superviseur']]])->isError);
    }

    public function test_a_super_administrator_the_creator_and_the_owner_are_never_touched(): void
    {
        $ctx = $this->as('admin');

        foreach (['root', 'creator', 'admin'] as $who) {
            $result = $this->roles()->handle($ctx, ['action' => 'assign', 'user_ids' => [$this->u[$who]], 'role_ids' => [$this->role['telepro']]]);

            $this->assertSame(ToolResult::PERMISSION_DENIED, $result->code, $who);
        }
        $this->assertSame([], $this->sent);
    }

    public function test_unknown_users_and_roles_the_limits_and_empty_requests_change_nothing(): void
    {
        $ctx = $this->as('admin');
        $bob = $this->u['bob'];
        $telepro = $this->role['telepro'];

        $this->assertSame(ToolResult::NOT_FOUND, $this->roles()->handle($ctx, ['action' => 'assign', 'user_ids' => [$this->u['outsider']], 'role_ids' => [$telepro]])->code);
        $this->assertSame(ToolResult::NOT_FOUND, $this->roles()->handle($ctx, ['action' => 'assign', 'user_ids' => [$bob], 'role_ids' => [99999]])->code);
        $this->assertSame(ToolResult::LIMIT_EXCEEDED, $this->roles()->handle($ctx, ['action' => 'assign', 'user_ids' => range(1, 11), 'role_ids' => [$telepro]])->code);
        $this->assertSame(ToolResult::VALIDATION_FAILED, $this->roles()->handle($ctx, ['action' => 'assign', 'user_ids' => [0], 'role_ids' => [$telepro]])->code);
        $this->assertSame([], $this->sent);
    }

    public function test_the_role_tool_is_always_decided_by_a_human_and_needs_the_right_on_user_roles(): void
    {
        $this->assertSame(UserRolesSetTool::DESTRUCTIVE, $this->roles()->risk());
        $this->assertSame('userRole', $this->roles()->ability());
    }

    public function test_what_will_be_refused_is_refused_before_the_human_is_asked(): void
    {
        $ctx = $this->as('admin');

        $this->assertSame(ToolResult::PERMISSION_DENIED, $this->roles()->precheck($ctx, ['action' => 'assign', 'user_ids' => [$this->u['creator']], 'role_ids' => [$this->role['telepro']]])->code);
        $this->assertNull($this->roles()->precheck($ctx, ['action' => 'assign', 'user_ids' => [$this->u['carol']], 'role_ids' => [$this->role['telepro']]]));
        $this->assertSame(ToolResult::PERMISSION_DENIED, $this->membership()->precheck($ctx, ['action' => 'detach', 'user_ids' => [$this->u['root']]])->code);
        $this->assertNull($this->membership()->precheck($ctx, ['action' => 'detach', 'user_ids' => [$this->u['bob']]]));

        $manager = $this->as('manager');
        $this->assertSame(ToolResult::PERMISSION_DENIED, $this->membership()->precheck($manager, ['action' => 'detach', 'user_ids' => [$this->u['bob']]])->code);
        $this->assertSame(ToolResult::PERMISSION_DENIED, $this->roles()->precheck($manager, ['action' => 'assign', 'user_ids' => [$this->u['carol']], 'role_ids' => [$this->role['superviseur']]])->code);
        // A check changes nothing
        $this->assertSame([], $this->sent);
    }

    // B — membership

    public function test_only_an_administrator_changes_who_is_in_the_project(): void
    {
        $result = $this->membership()->handle($this->as('manager'), ['action' => 'detach', 'user_ids' => [$this->u['bob']]]);

        $this->assertSame(ToolResult::PERMISSION_DENIED, $result->code);
        $this->assertSame([], $this->sent);
    }

    public function test_an_existing_account_is_attached_by_its_address(): void
    {
        $email = DB::table('users')->where('id', $this->u['outsider'])->value('email');

        $result = $this->membership()->handle($this->as('admin'), ['action' => 'attach', 'emails' => [strtoupper($email)]]);

        $this->assertFalse($result->isError);
        $this->assertSame([['POST', 'user', ['email' => $email]]], $this->sent);
    }

    public function test_an_unknown_address_a_member_and_a_protected_account_are_refused_without_any_call(): void
    {
        $ctx = $this->as('admin');
        $member = DB::table('users')->where('id', $this->u['bob'])->value('email');
        $root = DB::table('users')->where('id', $this->u['root'])->value('email');

        $this->assertSame(ToolResult::NOT_FOUND, $this->membership()->handle($ctx, ['action' => 'attach', 'emails' => ['nobody@example.test']])->code);
        $this->assertSame(ToolResult::INVALID_INPUT, $this->membership()->handle($ctx, ['action' => 'attach', 'emails' => [$member]])->code);
        $this->assertSame(ToolResult::PERMISSION_DENIED, $this->membership()->handle($ctx, ['action' => 'attach', 'emails' => [$root]])->code);
        $this->assertSame(ToolResult::VALIDATION_FAILED, $this->membership()->handle($ctx, ['action' => 'attach', 'emails' => []])->code);
        $this->assertSame([], $this->sent);
    }

    public function test_users_are_taken_out_of_the_project_but_never_the_protected_ones(): void
    {
        $ctx = $this->as('admin');

        $this->assertFalse($this->membership()->handle($ctx, ['action' => 'detach', 'user_ids' => [$this->u['bob'], $this->u['carol']]])->isError);
        $this->assertSame([['DELETE', "user/{$this->u['bob']}", []], ['DELETE', "user/{$this->u['carol']}", []]], $this->sent);

        $this->sent = [];
        foreach (['creator', 'root', 'admin'] as $who) {
            $this->assertSame(ToolResult::PERMISSION_DENIED, $this->membership()->handle($ctx, ['action' => 'detach', 'user_ids' => [$this->u[$who]]])->code, $who);
        }
        $this->assertSame(ToolResult::NOT_FOUND, $this->membership()->handle($ctx, ['action' => 'detach', 'user_ids' => [$this->u['outsider']]])->code);
        $this->assertSame([], $this->sent);
    }

    public function test_the_membership_tool_is_always_decided_by_a_human(): void
    {
        $this->assertSame(ProjectUserMembershipTool::DESTRUCTIVE, $this->membership()->risk());
    }
}
