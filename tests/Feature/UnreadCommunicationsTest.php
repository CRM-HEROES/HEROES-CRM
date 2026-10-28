<?php

namespace Tests\Feature;

use App\Http\Middleware\ProjectUserAccessCheck;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\UserLastProjectCheck;
use App\Http\Middleware\UserTrackActivity;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\User;
use App\Services\IncomingCommunications;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UnreadCommunicationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake([Authenticated::class]);
        config([
            'database.default' => 'unread_communications_test',
            'database.connections.unread_communications_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
        // Keep these tests independent of device tracking and last-activity writes.
        // The communication controller itself still requires an authenticated user.
        $this->withoutMiddleware([Authenticate::class, UserTrackActivity::class, ProjectUserAccessCheck::class, UserLastProjectCheck::class]);
        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('role')->default('super_admin');
        });
        $schema->create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->softDeletes();
        });
        $schema->create('prospects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->softDeletes();
        });
        $schema->create('threads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->softDeletes();
        });
        $schema->create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('thread_id')->default(1);
            $table->boolean('from_user')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->softDeletes();
        });
        $schema->create('user_message', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('message_id');
            $table->timestamp('archived_at')->nullable();
        });
        $schema->create('sms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospect_id');
            $table->boolean('from_user')->default(false);
            $table->boolean('is_incoming')->default(true);
        });
        $schema->create('interactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospect_id');
            $table->boolean('from_user')->default(false);
            $table->string('status')->nullable();
            $table->json('data')->nullable();
        });
        (require database_path('migrations/2026_10_20_000000_create_communication_reads_table.php'))->up();

        DB::table('users')->insert([['id' => 1], ['id' => 2]]);
        DB::table('projects')->insert([['id' => 1, 'slug' => 'test'], ['id' => 2, 'slug' => 'other']]);
        foreach ([1 => 1, 2 => 1, 3 => 2, 4 => 1] as $id => $projectId) {
            DB::table('prospects')->insert([
                'id' => $id, 'project_id' => $projectId,
                'deleted_at' => $id === 4 ? '2026-10-20 08:00:00' : null,
            ]);
        }
        DB::table('threads')->insert(['id' => 1, 'project_id' => 1]);
        foreach ([1, 2, 3] as $id) {
            DB::table('sms')->insert(['id' => $id, 'prospect_id' => 1]);
        }
        DB::table('sms')->insert([
            ['id' => 4, 'prospect_id' => 1, 'from_user' => true, 'is_incoming' => false],
            ['id' => 5, 'prospect_id' => 3, 'from_user' => false, 'is_incoming' => true],
            ['id' => 6, 'prospect_id' => 4, 'from_user' => false, 'is_incoming' => true],
        ]);
        foreach ([1, 2, 3, 4, 5, 6] as $id) {
            DB::table('messages')->insert([
                'id' => $id, 'prospect_id' => 1, 'from_user' => $id === 6,
                'archived_at' => $id === 3 ? '2026-10-20 08:00:00' : null,
                'deleted_at' => $id === 5 ? '2026-10-20 08:00:00' : null,
            ]);
        }
        DB::table('user_message')->insert(['user_id' => 1, 'message_id' => 4, 'archived_at' => '2026-10-20 08:00:00']);
        DB::table('interactions')->insert([
            ['id' => 1, 'prospect_id' => 1, 'from_user' => false, 'status' => 'voicemail', 'data' => null],
            ['id' => 2, 'prospect_id' => 1, 'from_user' => false, 'status' => 'answered', 'data' => json_encode(['recorded' => true])],
            ['id' => 3, 'prospect_id' => 1, 'from_user' => true, 'status' => 'voicemail', 'data' => null],
        ]);
    }

    protected function tearDown(): void
    {
        DB::purge('unread_communications_test');
        parent::tearDown();
    }

    public function test_six_unread_communications_from_one_prospect_count_as_six(): void
    {
        $this->assertSame(['count' => 6, 'sms' => 3, 'email' => 2, 'voicemail' => 1], $this->counts(1));
    }

    public function test_reading_sms_decrements_only_the_current_users_count_and_is_idempotent(): void
    {
        $this->actingAs(User::query()->findOrFail(1));
        $body = ['prospect_id' => 1, 'type' => 'sms', 'ids' => [1, 2, 4, 5], 'user_id' => 2];
        $this->postJson('/api/project/test/incoming-communications/read', $body)
            ->assertOk()->assertJson(['marked' => 2, 'count' => 4, 'sms' => 1]);
        $this->postJson('/api/project/test/incoming-communications/read', $body)
            ->assertOk()->assertJson(['marked' => 0, 'count' => 4]);
        $this->assertSame(7, $this->counts(2)['count']);
        $this->assertDatabaseCount('communication_reads', 2);
        $this->assertSame([1], DB::table('communication_reads')->distinct()->pluck('user_id')->all());
    }

    public function test_count_endpoint_returns_unread_messages_instead_of_prospects(): void
    {
        $this->actingAs(User::query()->findOrFail(1));
        $this->getJson('/api/project/test/incoming-communications/count')
            ->assertOk()->assertExactJson(['count' => 6, 'sms' => 3, 'email' => 2, 'voicemail' => 1]);
    }

    public function test_reading_email_and_listening_to_voicemail_decrement_their_counts(): void
    {
        $service = new IncomingCommunications();
        $project = Project::query()->findOrFail(1);
        $this->assertSame(2, $service->markRead($project, 1, 1, 'email', [1, 2, 6]));
        $this->assertSame(1, $service->markRead($project, 1, 1, 'voicemail', [1, 2, 3]));
        $this->assertSame(['count' => 3, 'sms' => 3, 'email' => 0, 'voicemail' => 0], $this->counts(1));
    }

    public function test_new_arrivals_remain_unread_after_older_messages_have_been_read(): void
    {
        $service = new IncomingCommunications();
        $service->markRead(Project::query()->findOrFail(1), 1, 1, 'sms', [1, 2, 3]);
        DB::table('sms')->insert(['prospect_id' => 1]);
        $this->assertSame(1, $this->counts(1)['sms']);
    }

    public function test_prospect_icons_receive_only_unread_incoming_ids_for_the_current_user(): void
    {
        $service = new IncomingCommunications();
        $project = Project::query()->findOrFail(1);
        $service->markRead($project, 1, 1, 'sms', [1, 2]);
        $service->markRead($project, 1, 1, 'email', [1]);
        $service->markRead($project, 1, 1, 'voicemail', [1]);
        $prospects = Prospect::query()->whereIn('id', [1, 2])->orderBy('id')->get();

        $service->appendUnread($prospects, $project, 1);
        $this->assertSame(['sms' => [3], 'email' => [2], 'voicemail' => []], $prospects[0]->unread_communications);
        $this->assertSame(['sms' => [], 'email' => [], 'voicemail' => []], $prospects[1]->unread_communications);

        $service->appendUnread($prospects, $project, 2);
        $this->assertSame(['sms' => [1, 2, 3], 'email' => [1, 2, 4], 'voicemail' => [1]], $prospects[0]->unread_communications);
    }

    public function test_prospect_unread_queries_are_batched_and_limited_to_the_current_page_and_project(): void
    {
        $service = new IncomingCommunications();
        $project = Project::query()->findOrFail(1);
        DB::table('sms')->insert(['id' => 7, 'prospect_id' => 2]);
        $prospects = Prospect::withTrashed()->whereIn('id', [1, 2, 3, 4])->orderBy('id')->get();

        DB::enableQueryLog();
        DB::flushQueryLog();
        $service->appendUnread($prospects, $project, 1);
        $this->assertCount(3, DB::getQueryLog());
        $this->assertSame([7], $prospects[1]->unread_communications['sms']);
        $this->assertSame([], $prospects[2]->unread_communications['sms']);
        $this->assertSame([], $prospects[3]->unread_communications['sms']);

        DB::flushQueryLog();
        $page = $prospects->take(1);
        $service->appendUnread($page, $project, 1);
        foreach (DB::getQueryLog() as $query) {
            $this->assertStringContainsString('"prospect_id" in (?)', $query['query']);
        }
        DB::flushQueryLog();
        $service->appendUnread(collect(), $project, 1);
        $this->assertSame([], DB::getQueryLog());
        DB::disableQueryLog();
    }

    public function test_cross_project_and_invalid_read_requests_are_rejected(): void
    {
        $this->actingAs(User::query()->findOrFail(1));
        $this->postJson('/api/project/test/incoming-communications/read', ['prospect_id' => 3, 'type' => 'sms', 'ids' => [5]])
            ->assertNotFound();
        $this->postJson('/api/project/test/incoming-communications/read', ['prospect_id' => 1, 'type' => 'call', 'ids' => [1]])
            ->assertUnprocessable();
        $this->assertDatabaseCount('communication_reads', 0);
    }

    public function test_authentication_is_required_to_count_or_mark_messages_read(): void
    {
        $this->getJson('/api/project/test/incoming-communications/count')->assertUnauthorized();
        $this->postJson('/api/project/test/incoming-communications/read', ['prospect_id' => 1, 'type' => 'sms', 'ids' => [1]])
            ->assertUnauthorized();
    }

    protected function counts(int $userId): array
    {
        return (new IncomingCommunications())->counts(Project::query()->findOrFail(1), $userId);
    }
}
