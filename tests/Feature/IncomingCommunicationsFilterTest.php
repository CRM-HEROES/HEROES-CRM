<?php

namespace Tests\Feature;

use App\Filters\ProspectRequestFilters;
use App\Http\Controllers\API\Project\ProspectController;
use App\Models\Project;
use App\Models\User;
use App\Services\IncomingCommunications;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class IncomingCommunicationsFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'incoming_communications_test',
            'database.connections.incoming_communications_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
        $schema = DB::connection()->getSchemaBuilder();
        $schema->create('projects', function (Blueprint $table) {
            $table->id();
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
            $table->unsignedBigInteger('thread_id')->nullable();
            $table->boolean('from_user')->default(true);
            $table->timestamp('archived_at')->nullable();
            $table->softDeletes();
        });
        $schema->create('sms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospect_id');
            $table->boolean('from_user')->default(true);
            $table->boolean('is_incoming')->default(false);
        });
        $schema->create('interactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospect_id');
            $table->boolean('from_user')->default(true);
            $table->string('status')->nullable();
            $table->json('data')->nullable();
        });
        $schema->create('communication_reads', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->string('communication_type');
            $table->unsignedBigInteger('communication_id');
        });
        $schema->create('user_message', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('message_id');
            $table->timestamp('archived_at')->nullable();
        });

        DB::table('projects')->insert([['id' => 1], ['id' => 2]]);
        foreach (range(1, 12) as $id) {
            DB::table('prospects')->insert([
                'id' => $id, 'project_id' => $id === 9 ? 2 : 1,
                'deleted_at' => $id === 10 ? '2026-10-19 10:00:00' : null,
            ]);
        }
        foreach ([1, 7, 9, 10] as $id) {
            DB::table('sms')->insert(['prospect_id' => $id, 'from_user' => false, 'is_incoming' => true]);
        }
        DB::table('sms')->insert([
            ['prospect_id' => 4, 'from_user' => true, 'is_incoming' => false],
            ['prospect_id' => 11, 'from_user' => false, 'is_incoming' => false],
            ['prospect_id' => 12, 'from_user' => true, 'is_incoming' => true],
        ]);
        DB::table('threads')->insert(['id' => 1, 'project_id' => 1]);
        DB::table('messages')->insert([
            ['prospect_id' => 2, 'thread_id' => 1, 'from_user' => false, 'deleted_at' => null],
            ['prospect_id' => 4, 'thread_id' => 1, 'from_user' => true, 'deleted_at' => null],
            ['prospect_id' => 7, 'thread_id' => 1, 'from_user' => false, 'deleted_at' => null],
            ['prospect_id' => 7, 'thread_id' => 1, 'from_user' => false, 'deleted_at' => null],
            ['prospect_id' => 8, 'thread_id' => 1, 'from_user' => false, 'deleted_at' => '2026-10-19 10:00:00'],
        ]);
        DB::table('interactions')->insert([
            ['prospect_id' => 3, 'from_user' => false, 'status' => 'voicemail', 'data' => null],
            ['prospect_id' => 4, 'from_user' => true, 'status' => 'voicemail', 'data' => null],
            ['prospect_id' => 5, 'from_user' => false, 'status' => 'answered', 'data' => json_encode(['recorded' => true])],
            ['prospect_id' => 7, 'from_user' => false, 'status' => 'voicemail', 'data' => null],
        ]);
    }

    protected function tearDown(): void
    {
        DB::purge('incoming_communications_test');
        parent::tearDown();
    }

    public function test_request_filter_lists_and_counts_received_communications_once_per_prospect(): void
    {
        $this->assertSame([1, 2, 3, 7, 11, 12], $this->ids());
        $count = (new ProspectController())->count($this->filters(), Project::query()->findOrFail(1));
        $this->assertSame(6, $count);
    }

    public function test_badge_counts_prospects_like_the_list_not_communications(): void
    {
        $project = Project::query()->findOrFail(1);
        $counts = app(IncomingCommunications::class)->counts($project, 1);

        // Prospect 7 has an unread SMS, two unread emails and a voicemail: it
        // is one prospect for every number, and one line in the list.
        $this->assertSame(['sms' => 4, 'email' => 2, 'voicemail' => 2, 'count' => 6], $counts);
        $this->assertSame(count($this->ids()), $counts['count']);
    }

    public function test_outgoing_messages_regular_recorded_calls_and_deleted_messages_are_excluded(): void
    {
        foreach ([4, 5, 6, 8] as $id) {
            $this->assertNotContains($id, $this->ids());
        }
    }

    public function test_other_project_and_deleted_prospects_are_excluded(): void
    {
        $this->assertNotContains(9, $this->ids());
        $this->assertNotContains(10, $this->ids());
        $this->assertSame([], Project::query()->findOrFail(1)->prospects()
            ->whereKey(4)->filter($this->filters())->pluck('id')->all());
    }

    public function test_disabled_filter_does_not_restrict_the_list(): void
    {
        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8, 11, 12], $this->ids(false));
    }

    public function test_processed_incoming_communications_are_excluded_for_every_user(): void
    {
        Event::fake([Authenticated::class]);
        auth()->setUser((new User())->forceFill(['id' => 1, 'role' => 'super_admin']));
        $smsId = DB::table('sms')->where('prospect_id', 1)->value('id');
        // Processed by another user: the current user must not see it either.
        DB::table('communication_reads')->insert([
            'user_id' => 2,
            'communication_type' => 'sms',
            'communication_id' => $smsId,
        ]);

        $this->assertNotContains(1, $this->ids());
        $this->assertContains(7, $this->ids());
    }

    /** @dataProvider voicemailData */
    public function test_voicemail_payloads_are_matched_and_false_flags_are_excluded(array $data, bool $matches): void
    {
        DB::table('interactions')->where('prospect_id', 3)->update([
            'status' => 'ended', 'data' => json_encode($data),
        ]);
        $this->assertSame($matches, in_array(3, $this->ids(), true));
    }

    public static function voicemailData(): array
    {
        return [
            'webhook flag' => [['is_voicemail' => true], true],
            'normalized flag' => [['has_voicemail' => true], true],
            'raw event' => [['cloudtalk_ended' => ['data' => ['is_voicemail' => true]]], true],
            'normalized call' => [['cloudtalk_call' => ['has_voicemail' => true]], true],
            'raw history' => [['cloudtalk_history' => ['Cdr' => ['is_voicemail' => true]]], true],
            'outcome' => [['outcome' => 'voicemail'], true],
            'recording link' => [['voicemail_url' => 'https://example.test/voicemail.wav'], true],
            'false flag' => [['is_voicemail' => false, 'recorded' => true], false],
            'null link' => [['voicemail_url' => null], false],
            'empty link' => [['voicemail_url' => ''], false],
        ];
    }

    protected function filters(bool $enabled = true): ProspectRequestFilters
    {
        request()->merge(['filters' => json_encode(['incomingCommunications' => $enabled])]);
        return new ProspectRequestFilters(request());
    }

    protected function ids(bool $enabled = true): array
    {
        return Project::query()->findOrFail(1)->prospects()
            ->filter($this->filters($enabled))->orderBy('id')->pluck('id')->all();
    }
}
