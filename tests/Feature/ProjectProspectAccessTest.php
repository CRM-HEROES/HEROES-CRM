<?php

namespace Tests\Feature;

use App\Http\Middleware\ProjectProspectCheck;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

/**
 * The prospects list shows every prospect of the project to a user having the
 * "prospectView" permission (ProspectScope). Opening one of those prospects -
 * its SMS, emails, calls... - must be allowed by the same rule.
 */
class ProjectProspectAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'prospect_access_test',
            'database.connections.prospect_access_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
        Event::fake([Authenticated::class]);

        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('role')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
        });
        DB::table('users')->insert([
            ['id' => 1, 'role' => 'super_admin', 'name' => 'Super', 'email' => 'super@example.test'],
            ['id' => 2, 'role' => null, 'name' => 'Agent', 'email' => 'agent@example.test'],
        ]);
        $schema->create('prospect_user', function (Blueprint $table) {
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('user_id');
        });
        $schema->create('prospect_group', function (Blueprint $table) {
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('group_id');
        });
        $schema->create('groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
        });
        $schema->create('user_group', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('group_id');
        });
        $schema->create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
        });
        $schema->create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });
        $schema->create('roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('name');
            $table->string('guard_name');
        });
        $schema->create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });
        $schema->create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
        });

        // The CRM catalog, as in production: "prospectView" resolves to
        // all.prospect.view and the policy then walks up to all.prospect.
        DB::table('permissions')->insert([
            ['id' => 1, 'name' => 'all', 'guard_name' => 'web'],
            ['id' => 2, 'name' => 'all.prospect', 'guard_name' => 'web'],
            ['id' => 3, 'name' => 'all.prospect.view', 'guard_name' => 'web'],
        ]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        DB::purge('prospect_access_test');
        setPermissionsTeamId(null);
        parent::tearDown();
    }

    public function test_a_member_who_may_see_all_prospects_opens_a_prospect_he_is_not_assigned_to(): void
    {
        $this->grant(2, ['all.prospect']);

        $this->assertSame('ok', $this->open(2, 1, 1)->getContent());
    }

    public function test_a_prospect_he_is_assigned_to_opens_without_the_permission(): void
    {
        DB::table('prospect_user')->insert(['prospect_id' => 123, 'user_id' => 2]);

        $this->assertSame('ok', $this->open(2, 1, 1)->getContent());
    }

    public function test_a_prospect_he_created_opens_without_the_permission(): void
    {
        $this->assertSame('ok', $this->open(2, 1, 1, creatorId: 2)->getContent());
    }

    public function test_a_member_without_the_permission_cannot_open_a_prospect_of_someone_else(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->open(2, 1, 1);
    }

    public function test_a_prospect_of_another_project_is_refused_even_with_the_permission(): void
    {
        $this->grant(2, ['all.prospect']);

        $this->expectException(NotFoundHttpException::class);

        $this->open(2, 1, 2);
    }

    /**
     * Run the middleware as the application does for
     * GET /api/project/{project}/prospect/{prospect}/sms
     */
    protected function open(int $userId, int $projectId, int $projectIdOfProspect, ?int $creatorId = null): Response
    {
        $user = (new User())->forceFill([
            'id' => $userId, 'role' => null, 'name' => 'Agent', 'email' => 'agent@example.test',
        ]);
        $user->exists = true;

        $project = (new Project())->forceFill(['id' => $projectId, 'slug' => 'test', 'creator_id' => 99]);
        $project->exists = true;

        $prospect = (new Prospect())->forceFill([
            'id' => 123, 'project_id' => $projectIdOfProspect,
            'creator_id' => $creatorId, 'processed_at' => null,
        ]);
        $prospect->exists = true;

        $this->actingAs($user);
        setPermissionsTeamId($projectId);

        // project / prospect are route parameters in the application
        $request = Request::create('/api/project/test/prospect/123/sms', 'GET', [
            'project' => $project,
            'prospect' => $prospect,
        ]);
        $this->app->instance('request', $request);

        return (new ProjectProspectCheck())->handle($request, fn () => new Response('ok'));
    }

    protected function grant(int $userId, array $permissions): void
    {
        foreach ($permissions as $name) {
            DB::table('model_has_permissions')->insert([
                'project_id' => 1,
                'permission_id' => DB::table('permissions')->where('name', $name)->value('id'),
                'model_type' => User::class,
                'model_id' => $userId,
            ]);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
