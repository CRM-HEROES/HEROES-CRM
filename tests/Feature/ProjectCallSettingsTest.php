<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
use Tests\Support\UsesInMemoryDatabase;
use Tests\TestCase;

class ProjectCallSettingsTest extends TestCase
{
    use UsesInMemoryDatabase;

    protected User $member;
    protected User $admin;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\UserTrackActivity::class);
        $this->setUpInMemoryDatabase();

        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('last_activity')->nullable();
            $table->string('ip_address')->nullable();
        });
        Schema::table('user_project', function (Blueprint $table): void {
            $table->timestamp('last_activity')->nullable();
            $table->unsignedInteger('relevance_event')->nullable();
            $table->unsignedInteger('relevance_prospect')->nullable();
            $table->unsignedInteger('relevance_order_action')->nullable();
            $table->unsignedInteger('relevance_message')->nullable();
            $table->unsignedInteger('prospects_count')->nullable();
        });
        Schema::table('projects', function (Blueprint $table): void {
            $table->boolean('calls_enabled')->default(false);
            $table->unsignedBigInteger('call_agent_id')->nullable();
            $table->unsignedBigInteger('call_transfer_user_id')->nullable();
            $table->unsignedBigInteger('call_back_label_id')->nullable();
        });
        Schema::create('project_settings', function (Blueprint $table): void {
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('key');
            $table->text('value')->nullable();
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('for');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('labels', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('user_category', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('category_id');
        });
        $this->member = User::find($this->makeUser('Call settings member'));
        $this->admin = User::find($this->makeUser('Call settings admin'));
        $projectId = $this->makeProject('Call settings project', $this->member->id);
        DB::table('user_project')->insert(['user_id' => $this->admin->id, 'project_id' => $projectId]);
        $this->project = Project::withoutGlobalScopes()->findOrFail($projectId);

        $permission = Permission::create(['name' => 'projectRoleUpdate', 'guard_name' => 'web']);
        app(PermissionRegistrar::class)->setPermissionsTeamId($projectId);
        $this->admin->givePermissionTo($permission);
        config(['calls.enabled' => true, 'calls.provider' => 'fake']);
    }

    public function test_project_admin_can_read_and_persist_call_settings(): void
    {
        $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/project/{$this->project->slug}/setting/calls")
            ->assertOk()
            ->assertJsonPath('calls_enabled', false)
            ->assertJsonPath('provider', 'fake')
            ->assertJsonPath('global_enabled', true);

        $this->putJson("/api/project/{$this->project->slug}/setting/calls", [
            'value' => [
                'calls_enabled' => true,
                'provider' => 'cloudtalk',
                'work_hours' => ['start' => '09:00', 'end' => '16:30'],
                'work_days' => [1, 2, 3, 4, 5],
                'timezone' => 'Europe/Paris',
                'greeting' => 'Bonjour',
                'transfer_available' => true,
            ],
        ])->assertOk();

        $this->assertTrue((bool) $this->project->fresh()->calls_enabled);
        $stored = json_decode(DB::table('project_settings')->where('project_id', $this->project->id)->where('key', 'calls')->value('value'), true);
        $this->assertSame('cloudtalk', $stored['provider']);
        $this->assertSame('cloudtalk', \App\Calls\CallSettings::provider($this->project));
        $this->assertSame('Europe/Paris', $stored['timezone']);
        $this->assertSame(['start' => '09:00', 'end' => '16:30'], $stored['work_hours']);
        $this->assertTrue($stored['transfer_available']);
    }

    public function test_non_admin_cannot_read_or_change_project_call_settings(): void
    {
        $this->actingAs($this->member, 'sanctum')
            ->getJson("/api/project/{$this->project->slug}/setting/calls")
            ->assertNotFound();

        $this->putJson("/api/project/{$this->project->slug}/setting/calls", [
            'value' => ['calls_enabled' => true],
        ])->assertNotFound();

        $this->postJson("/api/project/{$this->project->slug}/setting/calls/diagnostic", [
            'prospect_id' => 1,
        ])->assertNotFound();

        $this->putJson("/api/project/{$this->project->slug}", [
            'calls_enabled' => true,
        ])->assertNotFound();

        $this->assertFalse((bool) $this->project->fresh()->calls_enabled);
    }

    public function test_global_kill_switch_is_visible_and_super_admin_only(): void
    {
        $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/project/{$this->project->slug}/setting/calls", [
                'value' => ['global_enabled' => false],
            ])
            ->assertNotFound();

        $superAdmin = User::find($this->makeUser('Call settings super admin', null, 'super_admin'));
        $superAdmin->givePermissionTo(Permission::query()->where('name', 'projectRoleUpdate')->first());

        $this->actingAs($superAdmin, 'sanctum')
            ->putJson("/api/project/{$this->project->slug}/setting/calls", [
                'value' => ['global_enabled' => false],
            ])
            ->assertOk();

        $this->assertFalse(\App\Calls\CallSettings::globalEnabled());
        $this->assertSame(
            'false',
            DB::table('project_settings')->whereNull('project_id')->where('key', 'calls.global_enabled')->value('value')
        );

        config(['calls.enabled' => true]);
        $this->assertFalse(\App\Calls\CallSettings::globalEnabled(), 'La valeur en base doit primer sur la configuration.');

        $this->actingAs($superAdmin, 'sanctum')
            ->putJson("/api/project/{$this->project->slug}/setting/calls", [
                'value' => ['global_enabled' => true],
            ])
            ->assertOk();

        $this->assertTrue(\App\Calls\CallSettings::globalEnabled(), 'ON signifie que les communications sont autorisées.');
    }
}
