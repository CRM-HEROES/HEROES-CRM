<?php

namespace Tests\Feature;

use App\Console\Commands\Campaign as CampaignCommand;
use App\Filters\EventFilters;
use App\Models\Campaign;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Tests\TestCase;

/**
 * Campaign engine: scheduling selection, appointment campaigns,
 * keyset pagination and date format.
 */
class CampaignEngineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'campaign_engine_test',
            'database.connections.campaign_engine_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
            'campaign.timezone' => 'Europe/Paris',
        ]);

        $schema = DB::connection()->getSchemaBuilder();

        $schema->create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name')->default('c');
            $table->boolean('active')->default(true);
            $table->boolean('pending')->default(false);
            $table->boolean('processing')->default(false);
            $table->boolean('re_process_processed_prospects')->default(false);
            $table->boolean('error')->default(false);
            $table->string('for')->default('prospect');
            $table->string('frequency')->default('once');
            $table->timestamp('date')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('execution')->default(0);
            $table->softDeletes();
        });
        $schema->create('campaign_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->default(1);
            $table->string('type')->default('filter');
            $table->text('rule');
        });
        $schema->create('campaign_rule', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('rule_id');
            $table->boolean('error')->default(false);
        });
        $schema->create('campaign_operators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->default(1);
            $table->boolean('and')->default(true);
        });
        $schema->create('campaign_operator', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('operator_id');
        });
        $schema->create('calendars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->softDeletes();
        });
        $schema->create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calendar_id');
            $table->unsignedBigInteger('prospect_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->softDeletes();
        });
        $schema->create('campaign_event', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('action_id');
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    protected function campaign(array $attributes): int
    {
        return DB::table('campaigns')->insertGetId($attributes + ['project_id' => 1]);
    }

    /**
     * Ids the command would run for the given options.
     */
    protected function runIds(array $options): array
    {
        $command = new class extends CampaignCommand {
            public array $handled = [];

            public function handleCampaign(Campaign $campaign)
            {
                $this->handled[] = $campaign->id;
            }
        };
        $command->setLaravel($this->app);
        $command->run(new ArrayInput($options), new NullOutput());

        return $command->handled;
    }

    protected function eventCampaign(): Campaign
    {
        $campaign = new Campaign(['for' => 'event']);
        $campaign->id = 1;
        $campaign->project_id = 1;

        return $campaign;
    }

    // Scheduling

    public function test_once_campaign_runs_after_its_date_even_in_the_afternoon(): void
    {
        // 15:45 in Paris (UTC+1 at the end of October). With the former
        // 12-hour format ("h") this was compared as 03:45.
        Carbon::setTestNow(Carbon::parse('2026-10-29 14:45:00', 'UTC'));

        $due = $this->campaign(['frequency' => 'once', 'date' => '2026-10-29 15:30:00']);
        $this->campaign(['frequency' => 'once', 'date' => '2026-10-29 16:00:00']);                    // future
        $this->campaign(['frequency' => 'once', 'date' => '2026-10-28 08:00:00', 'execution' => 1]);  // already executed
        $this->campaign(['frequency' => 'once', 'date' => '2026-10-28 08:00:00', 'active' => false]);
        $this->campaign(['frequency' => 'once', 'date' => '2026-10-28 08:00:00', 'processing' => true]);
        $this->campaign(['frequency' => 'hourly', 'date' => '2026-10-28 08:00:00']);                  // other frequency

        $this->assertSame([$due], $this->runIds(['--frequency' => 'once']));
    }

    public function test_recurring_campaigns_only_run_for_their_own_frequency_once_started(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-29 14:45:00', 'UTC'));

        $hourly = $this->campaign(['frequency' => 'hourly', 'date' => '2026-10-01 09:00:00']);
        $this->campaign(['frequency' => 'hourly', 'date' => '2026-11-01 09:00:00']); // not started yet
        $this->campaign(['frequency' => 'daily', 'date' => '2026-10-01 09:00:00']);
        $this->campaign(['frequency' => 'every_fifteen_minutes', 'date' => '2026-10-01 09:00:00']);

        $this->assertSame([$hourly], $this->runIds(['--frequency' => 'hourly']));
    }

    public function test_daily_campaign_runs_at_the_hour_of_its_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-29 14:00:00', 'UTC')); // 15:00 Paris

        $due = $this->campaign(['frequency' => 'daily', 'date' => '2026-10-01 15:20:00']);
        $this->campaign(['frequency' => 'daily', 'date' => '2026-10-01 09:00:00']);

        $this->assertSame([$due], $this->runIds(['--frequency' => 'daily']));
    }

    public function test_monthly_campaign_runs_on_the_day_and_hour_of_its_date_clamped_to_month_end(): void
    {
        $due = $this->campaign(['frequency' => 'monthly', 'date' => '2026-08-31 15:00:00']);
        $this->campaign(['frequency' => 'monthly', 'date' => '2026-08-15 15:00:00']);

        // 31st: runs on the last day of a 30-day month
        Carbon::setTestNow(Carbon::parse('2026-11-30 14:10:00', 'UTC'));
        $this->assertSame([$due], $this->runIds(['--frequency' => 'monthly']));

        // Another day of the month
        Carbon::setTestNow(Carbon::parse('2026-10-29 14:10:00', 'UTC'));
        $this->assertSame([], $this->runIds(['--frequency' => 'monthly']));
    }

    public function test_manual_run_by_id_ignores_frequency_and_active_flag(): void
    {
        $id = $this->campaign(['frequency' => 'monthly', 'active' => false, 'date' => '2030-01-01 00:00:00']);

        $this->assertSame([$id], $this->runIds(['--id' => (string) $id]));
    }

    // Appointments

    public function test_event_filters_use_a_24_hour_format(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-29 15:00:00', 'UTC'));

        $query = Event::query();
        (new EventFilters())->startsInLessThan($query, 3600);

        $this->assertSame(['2026-10-29 16:00:00'], $query->getQuery()->getBindings());
    }

    public function test_event_campaign_matches_project_appointments_linked_to_a_prospect(): void
    {
        DB::table('calendars')->insert([
            ['id' => 1, 'project_id' => 1],
            ['id' => 2, 'project_id' => 2],
        ]);
        DB::table('events')->insert([
            ['id' => 1, 'calendar_id' => 1, 'prospect_id' => 10],
            ['id' => 2, 'calendar_id' => 1, 'prospect_id' => null], // no prospect: nothing to act on
            ['id' => 3, 'calendar_id' => 2, 'prospect_id' => 11],   // other project
        ]);

        $ids = $this->eventCampaign()->matching()->pluck('events.id')->all();

        $this->assertSame([1], $ids);
    }

    public function test_event_campaign_applies_its_rules(): void
    {
        DB::table('calendars')->insert(['id' => 1, 'project_id' => 1]);
        DB::table('events')->insert([
            ['id' => 1, 'calendar_id' => 1, 'prospect_id' => 10, 'started_at' => '2026-10-29 15:30:00'],
            ['id' => 2, 'calendar_id' => 1, 'prospect_id' => 10, 'started_at' => '2026-10-29 20:00:00'],
        ]);
        DB::table('campaign_rules')->insert(['id' => 1, 'rule' => json_encode(['startsInLessThan' => 3600])]);
        DB::table('campaign_rule')->insert(['campaign_id' => 1, 'rule_id' => 1]);

        // 15:00: only the 15:30 appointment starts within the hour
        Carbon::setTestNow(Carbon::parse('2026-10-29 15:00:00', 'UTC'));

        $ids = $this->eventCampaign()->matching()->pluck('events.id')->all();

        $this->assertSame([1], $ids);
    }

    public function test_unsupported_campaign_target_is_an_error_not_a_silent_null(): void
    {
        $campaign = new Campaign(['for' => 'unknown']);

        $this->expectException(\InvalidArgumentException::class);
        $campaign->matching();
    }

    public function test_unknown_rule_filter_is_reported_as_a_rule_error(): void
    {
        DB::table('calendars')->insert(['id' => 1, 'project_id' => 1]);
        DB::table('campaign_rules')->insert(['id' => 1, 'rule' => json_encode(['noSuchFilter' => 1])]);
        DB::table('campaign_rule')->insert(['campaign_id' => 1, 'rule_id' => 1]);

        // An undefined filter raises an \Error: it must be caught (and flagged
        // on the rule) instead of crashing the whole command.
        try {
            $this->eventCampaign()->matching();
            $this->fail('The invalid rule should have been rejected');
        } catch (\Throwable $e) {
            $this->assertSame(1, DB::table('campaign_rule')->where('error', true)->count());
        }
    }

    // Pagination

    public function test_keyset_pagination_does_not_skip_items_while_the_matching_set_shrinks(): void
    {
        DB::table('calendars')->insert(['id' => 1, 'project_id' => 1]);
        $events = [];
        for ($i = 1; $i <= 250; $i++) {
            $events[] = ['id' => $i, 'calendar_id' => 1, 'prospect_id' => 10];
        }
        DB::table('events')->insert($events);

        $campaign = $this->eventCampaign();
        $campaign->re_process_processed_prospects = false;
        $action = new \App\Models\CampaignAction();
        $action->id = 5;

        $command = new CampaignCommand();
        $matching = new \ReflectionMethod($command, 'matching');
        $matching->setAccessible(true);

        $seen = [];
        $lastId = 0;
        do {
            $page = $matching->invoke($command, $campaign, $action, $lastId, 100);

            foreach ($page as $event) {
                $seen[] = $event->id;
                // What the engine records for each processed item: the item
                // leaves the "not yet processed" set.
                DB::table('campaign_event')->insert([
                    'campaign_id' => 1, 'event_id' => $event->id, 'action_id' => 5,
                ]);
            }

            if ($page->isNotEmpty()) {
                $lastId = $page->last()->id;
            }
        } while ($page->count() >= 100);

        $this->assertSame(range(1, 250), $seen);
    }
}
