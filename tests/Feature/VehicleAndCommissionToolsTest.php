<?php

namespace Tests\Feature;

use App\Agents\AgentExecutionContext;
use App\Agents\Tools\CommissionsTool;
use App\Agents\Tools\ToolResult;
use App\Agents\Tools\VehiclesTool;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\UsesInMemoryDatabase;
use Tests\TestCase;

/**
 * Vehicles and commissions for the AI agents: read only, and the sensitive part (positions, pay) behind the right of who manages it.
 */
class VehicleAndCommissionToolsTest extends TestCase
{
    use UsesInMemoryDatabase;

    protected int $pid;
    protected int $otherPid;
    protected array $u = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpInMemoryDatabase();
        config(['logging.default' => 'null']);
        Event::fake([Authenticated::class]);

        Schema::create('vehicles', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('project_id');
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('name');
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();
            $t->string('tracker')->nullable();
            $t->timestamp('position_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('vehicle_positions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('vehicle_id');
            $t->unsignedBigInteger('user_id')->nullable();
            $t->decimal('latitude', 10, 7);
            $t->decimal('longitude', 10, 7);
            $t->timestamps();
        });
        foreach (['products', 'order_actions'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('project_id');
                $t->string('name');
                $t->timestamps();
                $t->softDeletes();
            });
        }
        Schema::create('commissions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('action_id');
            $t->unsignedBigInteger('product_id');
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('creator_id')->nullable();
            $t->string('name')->nullable();
            $t->string('type')->nullable();
            $t->decimal('value', 10, 2)->nullable();
            $t->boolean('including_tax')->default(false);
            $t->timestamps();
        });

        foreach (['creator', 'bob', 'driver'] as $name) {
            $this->u[$name] = $this->makeUser(ucfirst($name));
        }
        $this->pid = $this->makeProject('Alpha');
        $this->otherPid = $this->makeProject('Beta');
        DB::table('projects')->where('id', $this->pid)->update(['creator_id' => $this->u['creator']]);
        foreach ($this->u as $id) {
            DB::table('user_project')->insert(['user_id' => $id, 'project_id' => $this->pid]);
        }

        $now = now();
        DB::table('vehicles')->insert([
            ['id' => 1, 'project_id' => $this->pid, 'user_id' => $this->u['driver'], 'name' => 'Camionnette', 'latitude' => 45.7640001, 'longitude' => 4.8357001, 'tracker' => 'IMEI-SECRET', 'position_at' => '2026-11-18 09:00:00', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'project_id' => $this->otherPid, 'user_id' => null, 'name' => 'Autre projet', 'latitude' => 1.0, 'longitude' => 1.0, 'tracker' => null, 'position_at' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
        for ($i = 0; $i < 8; $i++) {
            DB::table('vehicle_positions')->insert(['vehicle_id' => 1, 'user_id' => $this->u['driver'], 'latitude' => 45.764 + $i / 10000, 'longitude' => 4.8357 + $i / 10000, 'created_at' => now()->subMinutes(100 - $i), 'updated_at' => $now]);
        }
        // A position of the vehicle written by somebody else is not the driver's
        DB::table('vehicle_positions')->insert(['vehicle_id' => 1, 'user_id' => $this->u['bob'], 'latitude' => 10, 'longitude' => 10, 'created_at' => now(), 'updated_at' => $now]);

        DB::table('products')->insert([['id' => 1, 'project_id' => $this->pid, 'name' => 'Pompe', 'created_at' => $now, 'updated_at' => $now], ['id' => 2, 'project_id' => $this->otherPid, 'name' => 'Autre', 'created_at' => $now, 'updated_at' => $now]]);
        DB::table('order_actions')->insert([['id' => 1, 'project_id' => $this->pid, 'name' => 'Vente', 'created_at' => $now, 'updated_at' => $now], ['id' => 2, 'project_id' => $this->otherPid, 'name' => 'Autre', 'created_at' => $now, 'updated_at' => $now]]);
        DB::table('commissions')->insert([
            ['action_id' => 1, 'product_id' => 1, 'user_id' => $this->u['driver'], 'type' => 'percent', 'value' => 5.5, 'including_tax' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['action_id' => 1, 'product_id' => 1, 'user_id' => $this->u['bob'], 'type' => 'fixed', 'value' => 20, 'including_tax' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['action_id' => 2, 'product_id' => 2, 'user_id' => $this->u['driver'], 'type' => 'percent', 'value' => 99, 'including_tax' => 0, 'created_at' => $now, 'updated_at' => $now],
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

    public function test_the_vehicles_are_listed_without_coordinates_or_tracker(): void
    {
        $result = (new VehiclesTool())->handle($this->ctx('bob'), ['view' => 'list']);

        $this->assertFalse($result->isError);
        $this->assertSame('Camionnette', $result->data['vehicles'][0]['name']);
        $this->assertSame('Driver', $result->data['vehicles'][0]['user']);
        $this->assertNotNull($result->data['vehicles'][0]['last_position_at']);
        $json = json_encode($result->data);
        $this->assertStringNotContainsString('45.76', $json);
        $this->assertStringNotContainsString('IMEI', $json);
        $this->assertStringNotContainsString('Autre projet', $json);
    }

    public function test_the_positions_need_the_right_to_manage_vehicles(): void
    {
        $denied = (new VehiclesTool())->handle($this->ctx('bob'), ['view' => 'position', 'vehicle_id' => 1]);

        $this->assertSame(ToolResult::PERMISSION_DENIED, $denied->code);
    }

    public function test_the_last_positions_are_few_rounded_and_the_ones_of_the_driver_only(): void
    {
        $result = (new VehiclesTool())->handle($this->ctx('creator'), ['view' => 'position', 'vehicle_id' => 1]);

        $this->assertFalse($result->isError);
        $this->assertCount(VehiclesTool::POSITIONS, $result->data['positions']);
        foreach ($result->data['positions'] as $position) {
            $this->assertSame(round($position['latitude'], 3), $position['latitude']);
            $this->assertGreaterThanOrEqual(45.764, $position['latitude']);
            $this->assertLessThanOrEqual(45.765, $position['latitude']);
        }
        $this->assertStringNotContainsString('10', json_encode(array_column($result->data['positions'], 'longitude')), 'La position écrite par un autre utilisateur n\'est pas celle du conducteur');
    }

    public function test_a_vehicle_of_another_project_is_not_found(): void
    {
        $this->assertSame(ToolResult::NOT_FOUND, (new VehiclesTool())->handle($this->ctx('creator'), ['view' => 'position', 'vehicle_id' => 2])->code);
        $this->assertSame(ToolResult::NOT_FOUND, (new VehiclesTool())->handle($this->ctx('creator'), ['view' => 'position'])->code);
    }

    public function test_the_commissions_are_those_of_the_project_with_names_and_amounts(): void
    {
        $result = (new CommissionsTool())->handle($this->ctx('creator'), []);

        $this->assertFalse($result->isError);
        $this->assertSame(2, $result->data['total']);
        $byUser = array_column($result->data['commissions'], null, 'user');
        $this->assertSame('percent', $byUser['Driver']['type']);
        $this->assertEquals(5.5, $byUser['Driver']['value']);
        $this->assertTrue($byUser['Bob']['including_tax']);
        $this->assertSame('Pompe', $byUser['Bob']['product']);
        $this->assertStringNotContainsString('99', json_encode($result->data), 'Une commission d\'un autre projet n\'est pas là');
    }

    public function test_the_commissions_can_be_narrowed_to_one_user(): void
    {
        $result = (new CommissionsTool())->handle($this->ctx('creator'), ['user_id' => $this->u['bob']]);

        $this->assertSame(['Bob'], array_column($result->data['commissions'], 'user'));
    }

    public function test_the_commissions_declare_the_right_of_who_manages_them_and_everything_only_reads(): void
    {
        $this->assertSame('projectUserCommissionAdd', (new CommissionsTool())->ability());
        $this->assertSame(CommissionsTool::READ, (new CommissionsTool())->risk());
        $this->assertSame(VehiclesTool::READ, (new VehiclesTool())->risk());
    }
}
