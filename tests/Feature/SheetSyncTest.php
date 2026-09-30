<?php

namespace Tests\Feature;

use App\Jobs\ProcessSheetSyncRows;
use App\Models\ImportSyncRow;
use App\Services\Import\SheetSyncProcessor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Google Sheets instant sync: webhook -> inbox -> prospects.
 * Every test runs in a transaction that is rolled back.
 */
class SheetSyncTest extends TestCase
{
    use DatabaseTransactions;

    protected int $importId;
    protected int $projectId;
    protected string $token;

    protected const HEADERS = ['id', 'created_time', 'email', 'full_name', 'phone_number'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = bin2hex(random_bytes(10));
        $this->projectId = DB::table('projects')->insertGetId([
            'name' => 'sheet sync test',
            'slug' => 'sheet-sync-' . bin2hex(random_bytes(4)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $emailField = DB::table('fields')->insertGetId([
            'project_id' => $this->projectId, 'name' => 'email', 'slug' => 'email',
            'for' => 'prospect', 'meta' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->importId = DB::table('imports')->insertGetId([
            'project_id' => $this->projectId,
            'name' => 'sync',
            'source' => 'google_sheets',
            'source_url' => 'https://docs.google.com/spreadsheets/d/x',
            'headers' => json_encode(self::HEADERS),
            'mapping' => json_encode(['meta->id', 'created_at', 'email', 'full_name', 'mobile_phone_number']),
            'duplicates_fields' => json_encode([$emailField]),
            'sync_enabled' => 1,
            'token' => $this->token,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function rows(int $from, int $count): array
    {
        $rows = [];
        for ($n = $from; $n < $from + $count; $n++) {
            $rows[] = ['key' => "id:l:$n", 'values' => [
                "l:$n", '2026-07-15T09:31:50-05:00', "lead$n@example.com", "Prenom$n NOM$n", 'p:+336' . str_pad((string) $n, 8, '0', STR_PAD_LEFT),
            ]];
        }

        return $rows;
    }

    protected function send(array $rows, ?string $signature = null, ?string $token = null)
    {
        $body = json_encode(['batch_id' => uniqid('b'), 'sheet' => 'Maubeuge', 'headers' => self::HEADERS, 'rows' => $rows]);
        $signature = $signature ?? hash_hmac('sha256', $body, $token ?? $this->token);

        return $this->call('POST', "/api/webservice/{$this->importId}/sheet-sync", [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_SIGNATURE' => $signature,
            'HTTP_ACCEPT' => 'application/json',
        ], $body);
    }

    protected function process(): array
    {
        return app(SheetSyncProcessor::class)->process(
            \App\Models\Import::withoutGlobalScopes()->find($this->importId)
        );
    }

    public function test_it_rejects_an_unsigned_or_wrongly_signed_request(): void
    {
        Queue::fake();

        $this->send($this->rows(1, 1), 'deadbeef')->assertStatus(403);
        $this->send($this->rows(1, 1), null, 'another-secret')->assertStatus(403);
        $this->assertSame(0, DB::table('import_sync_rows')->where('import_id', $this->importId)->count());
    }

    public function test_it_refuses_an_import_without_instant_sync(): void
    {
        Queue::fake();
        DB::table('imports')->where('id', $this->importId)->update(['sync_enabled' => 0]);

        $this->send($this->rows(1, 1))->assertStatus(409);
    }

    public function test_it_stores_the_rows_then_creates_each_prospect_once(): void
    {
        Queue::fake();

        $this->send($this->rows(1, 100))
            ->assertStatus(202)
            ->assertJson(['received' => 100, 'new' => 100, 'duplicates' => 0]);
        Queue::assertPushed(ProcessSheetSyncRows::class);

        $this->assertSame(100, DB::table('import_sync_rows')->where('import_id', $this->importId)->where('status', 'pending')->count());
        $this->assertSame(0, DB::table('prospects')->where('import_id', $this->importId)->count());

        $this->assertSame(['created' => 100, 'skipped' => 0], $this->process());

        // The same webhook again (retry, duplicate delivery): nothing new.
        $this->send($this->rows(1, 100))->assertStatus(202)->assertJson(['new' => 0, 'duplicates' => 100]);
        $this->assertSame(['created' => 0, 'skipped' => 0], $this->process());

        $this->assertSame(100, DB::table('prospects')->where('import_id', $this->importId)->count());
        $this->assertSame(100, DB::table('import_sync_rows')->where('import_id', $this->importId)->where('status', 'created')->count());

        $prospect = DB::table('prospects')->where('email', 'lead7@example.com')->first();
        $this->assertSame('Prenom7', $prospect->first_name);
        $this->assertSame('NOM7', $prospect->last_name);
        $this->assertSame('+33600000007', $prospect->mobile_phone_number);
    }

    public function test_it_never_modifies_an_existing_prospect(): void
    {
        Queue::fake();

        $id = DB::table('prospects')->insertGetId([
            'project_id' => $this->projectId, 'import_id' => null, 'email' => 'lead1@example.com',
            'first_name' => 'MANUEL', 'last_name' => 'CRM', 'meta' => json_encode(['note' => 'edited by an agent']),
            'created_at' => '2026-01-01 10:00:00', 'updated_at' => '2026-01-02 11:00:00',
        ]);
        $before = (array) DB::table('prospects')->where('id', $id)->first();

        $this->send($this->rows(1, 3))->assertStatus(202);
        $this->assertSame(['created' => 2, 'skipped' => 1], $this->process());

        $this->assertSame($before, (array) DB::table('prospects')->where('id', $id)->first());
        $this->assertSame(0, DB::table('prospects')->where('import_id', $this->importId)->where('email', 'lead1@example.com')->count());
        $this->assertSame($id, (int) DB::table('import_sync_rows')->where('external_id', 'id:l:1')->value('prospect_id'));
    }

    public function test_a_row_edited_in_the_sheet_is_not_applied_nor_duplicated(): void
    {
        Queue::fake();

        $this->send($this->rows(1, 1));
        $this->process();

        $edited = $this->rows(1, 1);
        $edited[0]['values'][2] = 'changed@example.com';
        $edited[0]['values'][3] = 'Someone Else';

        $this->send($edited)->assertJson(['new' => 0, 'duplicates' => 1]);
        $this->process();

        $this->assertSame(1, DB::table('prospects')->where('import_id', $this->importId)->count());
        $this->assertSame('lead1@example.com', DB::table('prospects')->where('import_id', $this->importId)->value('email'));
    }

    public function test_the_same_person_under_two_sheet_ids_is_created_once(): void
    {
        Queue::fake();

        $rows = $this->rows(1, 1);
        $again = $rows[0];
        $again['key'] = 'id:l:999';
        $this->send([$rows[0], $again])->assertStatus(202);

        $this->assertSame(['created' => 1, 'skipped' => 1], $this->process());
        $this->assertSame(1, DB::table('prospects')->where('import_id', $this->importId)->count());
    }

    public function test_a_failed_batch_leaves_nothing_half_saved_and_is_retried(): void
    {
        Queue::fake();
        $this->send($this->rows(1, 10));

        $crashing = new class extends SheetSyncProcessor {
            protected function markProcessed($rows, array $results): void
            {
                throw new \RuntimeException('crash before commit');
            }
        };

        try {
            $crashing->process(\App\Models\Import::withoutGlobalScopes()->find($this->importId));
            $this->fail('the simulated crash should have been thrown');
        } catch (\RuntimeException $e) {
            $this->assertSame('crash before commit', $e->getMessage());
        }

        $this->assertSame(0, DB::table('prospects')->where('import_id', $this->importId)->count());
        $this->assertSame(10, DB::table('import_sync_rows')->where('import_id', $this->importId)->where('status', 'pending')->count());

        $this->assertSame(['created' => 10, 'skipped' => 0], $this->process());
    }

    public function test_rows_claimed_by_a_dead_worker_are_recovered(): void
    {
        Queue::fake();
        $this->send($this->rows(1, 5));

        DB::table('import_sync_rows')->where('import_id', $this->importId)->update([
            'status' => ImportSyncRow::STATUS_PROCESSING, 'claim_token' => 'dead', 'claimed_at' => now()->subMinutes(30),
        ]);

        $this->assertSame(0, app(SheetSyncProcessor::class)->claim($this->importId)->count());
        $this->assertGreaterThanOrEqual(5, app(SheetSyncProcessor::class)->recoverStaleClaims());
        $this->assertSame(5, DB::table('import_sync_rows')->where('import_id', $this->importId)->where('status', 'pending')->count());
        $this->assertSame(['created' => 5, 'skipped' => 0], $this->process());
    }

    public function test_the_webhook_still_answers_202_when_the_queue_is_unavailable(): void
    {
        // Putting the sync job on the queue fails (as when Redis is down);
        // every other job / event keeps working.
        $this->app->instance(
            \Illuminate\Contracts\Bus\Dispatcher::class,
            new class($this->app, fn ($connection = null) => $this->app->make(\Illuminate\Contracts\Queue\Factory::class)->connection($connection)) extends \Illuminate\Bus\Dispatcher {
                public function dispatch($command)
                {
                    if ($command instanceof ProcessSheetSyncRows) {
                        throw new \RuntimeException('queue down');
                    }

                    return parent::dispatch($command);
                }
            }
        );

        $this->send($this->rows(1, 3))->assertStatus(202)->assertJson(['new' => 3]);
        $this->assertSame(3, DB::table('import_sync_rows')->where('import_id', $this->importId)->where('status', 'pending')->count());
    }
}
