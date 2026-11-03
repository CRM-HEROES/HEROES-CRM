<?php

namespace Tests\Support;

use App\Agents\AgentExecutionContext;
use App\Agents\Entities\ReadGateway;
use App\Agents\Entities\StatsGateway;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * A project "Alpha" with its team, prospects, calls, SMS, appointments and
 * orders, on the REAL tables of the CRM (MySQL), inside a transaction that is
 * rolled back: AGENTS_TEST_DB=mysql php vendor/bin/phpunit --filter <test>
 *
 *   admin       creator of Alpha: sees everything
 *   t1          téléconseiller (no "see every prospect")
 *   t2          téléconseiller, assignable by t1
 *   t3          superviseur, may see every prospect
 *   bare        member without any role
 *   stranger    member of Beta only
 *
 * Prospects of Alpha: p1 (created by t1), p2 (assigned to t1), p3 (in a group of
 * t1), p4 (nobody's), p5 (processed). t1 therefore sees p1, p2 and p3.
 */
abstract class CrmFixtureTestCase extends TestCase
{
    use UsesInMemoryDatabase;

    /** @var array<string, int> */
    protected array $u = [];

    /** @var array<string, int> */
    protected array $p = [];

    protected int $pid;

    protected int $beta;

    protected function setUp(): void
    {
        parent::setUp();

        if (env('AGENTS_TEST_DB') !== 'mysql') {
            $this->markTestSkipped('Ces tests lisent les vraies tables du CRM : lancez-les avec AGENTS_TEST_DB=mysql (transaction annulée).');
        }

        $this->setUpInMemoryDatabase();
        config(['logging.default' => 'null']);
        $this->buildData();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function buildData(): void
    {
        foreach (['admin' => 'Admin', 't1' => 'Tele1', 't2' => 'Tele2', 't3' => 'Superviseur', 'bare' => 'Bare', 'stranger' => 'Stranger'] as $key => $name) {
            $this->u[$key] = $this->makeUser($name);
        }

        $this->pid = $this->makeProject('Alpha');
        DB::table('projects')->where('id', $this->pid)->update(['creator_id' => $this->u['admin']]);
        $this->beta = $this->makeProject('Beta', $this->u['stranger']);

        foreach (['admin', 't1', 't2', 't3', 'bare'] as $key) {
            DB::table('user_project')->insert(['user_id' => $this->u[$key], 'project_id' => $this->pid]);
        }

        $this->makeRole('Télépro', ['all.prospect.add', 'all.prospect.update', 'all.prospect.sms', 'all.prospect.interaction', 'all.prospect.order', 'all.prospect.event'], ['t1', 't2']);
        $this->makeRole('Superviseur', ['all.prospect'], ['t3']);

        DB::table('user_assignable_user')->insert(['user_id' => $this->u['t1'], 'assignable_user_id' => $this->u['t2'], 'project_id' => $this->pid]);

        // Prospects
        $this->p['p1'] = $this->makeProspect(['first_name' => 'Jean', 'last_name' => 'Dupont', 'company_name' => 'Acme', 'email' => 'jean@acme.test', 'city' => 'Lyon', 'creator_id' => $this->u['t1'], 'created_at' => '2026-10-10 09:00:00']);
        $this->p['p2'] = $this->makeProspect(['last_name' => 'Martin', 'city' => 'Paris', 'creator_id' => $this->u['admin'], 'created_at' => '2026-10-12 09:00:00']);
        $this->p['p3'] = $this->makeProspect(['last_name' => 'Durand', 'creator_id' => $this->u['admin'], 'created_at' => '2026-10-14 09:00:00']);
        $this->p['p4'] = $this->makeProspect(['last_name' => 'Bernard', 'creator_id' => $this->u['admin'], 'created_at' => '2026-10-16 09:00:00']);
        $this->p['p5'] = $this->makeProspect(['last_name' => 'Petit', 'creator_id' => $this->u['admin'], 'created_at' => '2026-10-18 09:00:00', 'processed_at' => '2026-10-19 09:00:00', 'processed_by' => $this->u['admin']]);
        $this->p['q1'] = $this->makeProspect(['last_name' => 'Etranger', 'project_id' => $this->beta, 'creator_id' => $this->u['stranger']]);

        DB::table('prospect_user')->insert(['prospect_id' => $this->p['p2'], 'user_id' => $this->u['t1']]);
        $group = DB::table('groups')->insertGetId(['project_id' => $this->pid, 'name' => 'Equipe A', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('user_group')->insert(['user_id' => $this->u['t1'], 'group_id' => $group]);
        DB::table('prospect_group')->insert(['prospect_id' => $this->p['p3'], 'group_id' => $group]);
        $this->group = $group;

        // Calls: A = 12-18 Oct, B = 5-11 Oct
        $this->makeCall('p1', 't1', '2026-10-13 10:00:00', 300, 'answered');
        $this->makeCall('p1', 't1', '2026-10-14 11:00:00', 30, 'no_answer');
        $this->makeCall('p2', 't1', '2026-10-15 09:00:00', 600, 'answered');
        $this->makeCall('p4', 't2', '2026-10-16 15:00:00', 120, 'answered');
        $this->makeCall('p1', 't1', '2026-10-07 10:00:00', 60, 'answered');
        $this->makeCall('p4', 't2', '2026-10-08 10:00:00', 60, 'answered');
        $this->makeCall('p4', 't2', '2026-10-09 10:00:00', null, 'no_answer');
        $this->makeCall('q1', 'stranger', '2026-10-13 10:00:00', 60, 'answered');

        // SMS
        $this->makeSms('p1', 't1', 'Bonjour Jean, voici notre offre.', 1, '2026-10-13 12:00:00');
        $this->makeSms('p4', 't2', 'Message sur une fiche non visible', 1, '2026-10-16 12:00:00');
        $this->makeSms('p2', 't1', 'Merci, je rappelle demain', 0, '2026-10-15 16:00:00');

        // Appointments
        $this->calendar = DB::table('calendars')->insertGetId(['project_id' => $this->pid, 'name' => 'RDV Tel', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('user_calendar')->insert(['user_id' => $this->u['t1'], 'calendar_id' => $this->calendar]);
        $this->makeEvent('RDV Dupont', 'p1', 't1', '2026-10-20 10:00:00');
        $this->makeEvent('RDV Bernard', 'p4', 't2', '2026-10-21 10:00:00');
        $this->makeEvent('Tâche de t1', null, 't1', '2026-10-22 10:00:00');
        $this->makeEvent('RDV supprimé', 'p1', 't1', '2026-10-23 10:00:00', now());

        // Orders
        $status = DB::table('order_status')->insertGetId(['project_id' => $this->pid, 'name' => 'Nouveau', 'created_at' => now(), 'updated_at' => now()]);
        $this->makeOrder('p1', 't1', 1, $status);
        $this->makeOrder('p4', 't2', 2, $status);
        $this->makeOrder('p2', 't1', 3, $status);
    }

    protected int $group;

    protected int $calendar;

    protected function makeRole(string $name, array $permissions, array $users): void
    {
        $role = DB::table('roles')->insertGetId(['project_id' => $this->pid, 'name' => $name, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);

        foreach ($permissions as $permission) {
            $id = DB::table('permissions')->where('name', $permission)->where('guard_name', 'web')->value('id')
                ?? DB::table('permissions')->insertGetId(['name' => $permission, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);

            DB::table('role_has_permissions')->insert(['permission_id' => $id, 'role_id' => $role]);
        }

        foreach ($users as $user) {
            DB::table('model_has_roles')->insert(['role_id' => $role, 'model_type' => User::class, 'model_id' => $this->u[$user], 'project_id' => $this->pid]);
        }
    }

    protected function makeProspect(array $attributes): int
    {
        return DB::table('prospects')->insertGetId($attributes + [
            'project_id' => $this->pid,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function makeCall(string $prospect, string $user, string $start, ?int $seconds, string $status): void
    {
        DB::table('interactions')->insert([
            'prospect_id' => $this->p[$prospect],
            'creator_id' => $this->u[$user],
            'from_user' => 1,
            'started_at' => $start,
            'ended_at' => $seconds === null ? null : date('Y-m-d H:i:s', strtotime($start) + $seconds),
            'source' => 'cloudtalk',
            'status' => $status,
            'number' => '+33600000000',
            'created_at' => $start,
            'updated_at' => $start,
        ]);
    }

    protected function makeSms(string $prospect, string $user, string $message, int $fromUser, string $sentAt): void
    {
        DB::table('sms')->insert([
            'prospect_id' => $this->p[$prospect],
            'creator_id' => $this->u[$user],
            'message' => $message,
            'from_user' => $fromUser,
            'source' => 'smsbox',
            'sent_at' => $sentAt,
            'created_at' => $sentAt,
            'updated_at' => $sentAt,
        ]);
    }

    protected function makeEvent(string $name, ?string $prospect, string $user, string $start, $deletedAt = null): void
    {
        DB::table('events')->insert([
            'calendar_id' => $this->calendar,
            'prospect_id' => $prospect ? $this->p[$prospect] : null,
            'user_id' => $this->u[$user],
            'name' => $name,
            'started_at' => $start,
            'ended_at' => date('Y-m-d H:i:s', strtotime($start) + 1800),
            'deleted_at' => $deletedAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function makeOrder(string $prospect, string $user, int $number, int $status): void
    {
        DB::table('orders')->insert([
            'prospect_id' => $this->p[$prospect],
            'creator_id' => $this->u[$user],
            'status_id' => $status,
            'name' => "Commande $number",
            'number' => $number,
            'created_at' => '2026-10-15 10:00:00',
            'updated_at' => '2026-10-15 10:00:00',
        ]);
    }

    // Helpers

    protected function actAs(string $who, \Closure $callback): mixed
    {
        $context = new AgentExecutionContext(User::find($this->u[$who]), Project::withoutGlobalScopes()->find($this->pid));

        return $context->run($callback);
    }

    protected function search(string $who, string $entity, array $params = []): array
    {
        return $this->actAs($who, fn ($context) => app(ReadGateway::class)->search($context, $entity, $params));
    }

    protected function ids(array $result): array
    {
        $ids = array_column($result['rows'], 'id');
        sort($ids);

        return $ids;
    }

    protected function expected(string ...$keys): array
    {
        $ids = array_map(fn ($key) => $this->p[$key], $keys);
        sort($ids);

        return $ids;
    }

    protected const A = ['preset' => 'custom', 'from' => '2026-10-12', 'to' => '2026-10-18'];

    protected const B = ['preset' => 'custom', 'from' => '2026-10-05', 'to' => '2026-10-11'];
}
