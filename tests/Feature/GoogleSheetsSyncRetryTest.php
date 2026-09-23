<?php

namespace Tests\Feature;

use App\Jobs\GoogleSheetSyncRequest;
use App\Jobs\ImportProspects;
use App\Models\Import;
use App\Services\Import\GoogleSheetSyncer;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class GoogleSheetsSyncRetryTest extends TestCase
{
    public function test_it_requeues_a_sync_when_the_import_is_already_running(): void
    {
        Bus::fake();

        $import = new Import([
            'id' => 42,
            'source' => 'google_sheets',
            'sync_enabled' => true,
        ]);

        app(GoogleSheetSyncer::class)->queueRetryIfBusy($import);

        Bus::assertDispatched(GoogleSheetSyncRequest::class, function ($job) use ($import) {
            return $job->import->id === $import->id;
        });
    }

    public function test_it_ignores_any_row_matching_an_existing_crm_prospect_and_keeps_database_values_authoritative(): void
    {
        $job = (new ReflectionClass(ImportProspects::class))->newInstanceWithoutConstructor();

        $setProperty = function (string $name, mixed $value) use ($job): void {
            $property = (new ReflectionClass($job))->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($job, $value);
        };

        $setProperty('existingEmails', [
            'john.doe@example.com' => ['id' => 99, 'import_id' => 7],
        ]);
        $setProperty('existingPhones', [
            '+33612345678' => ['id' => 99, 'import_id' => 7],
        ]);
        $setProperty('existingMobiles', [
            '+33612345678' => ['id' => 99, 'import_id' => 7],
        ]);
        $setProperty('import', new Import(['id' => 42, 'project_id' => 1]));

        $method = (new ReflectionClass($job))->getMethod('findExistingDuplicate');
        $method->setAccessible(true);

        $duplicate = $method->invoke($job, [
            'email' => 'john.doe@example.com',
            'phone_number' => '06 12 34 56 78',
            'mobile_phone_number' => '0612345678',
        ]);

        $this->assertNotNull($duplicate);
        $this->assertSame(99, $duplicate['id']);
        $this->assertFalse($duplicate['sameImport']);
    }

    public function test_it_dispatches_immediate_retry_when_google_sheet_sync_is_busy(): void
    {
        Bus::fake();

        $import = new Import([
            'id' => 42,
            'source' => 'google_sheets',
            'sync_enabled' => true,
        ]);

        app(GoogleSheetSyncer::class)->queueRetryIfBusy($import);

        Bus::assertDispatched(GoogleSheetSyncRequest::class, function ($job) use ($import) {
            return $job->import->id === $import->id && $job->delay === null;
        });
    }

    public function test_it_recovers_a_stale_processing_lock_for_google_sheet_imports(): void
    {
        $import = Mockery::mock(Import::class)->makePartial();
        $import->forceFill([
            'id' => 42,
            'is_processing' => true,
            'processed_at' => now()->subMinutes(5),
            'updated_at' => now()->subMinutes(15),
            'created_at' => now()->subMinutes(15),
        ]);
        $import->shouldReceive('saveQuietly')->once()->andReturnUsing(function () use ($import) {
            $import->is_processing = false;
            $import->processed_at = null;

            return true;
        });

        $result = app(GoogleSheetSyncer::class)->clearStaleProcessingLockIfNeeded($import);

        $this->assertTrue($result);
        $this->assertFalse($import->is_processing);
        $this->assertNull($import->processed_at);
    }

    public function test_it_never_deletes_existing_prospects_for_google_sheet_auto_sync(): void
    {
        $job = (new ReflectionClass(ImportProspects::class))->newInstanceWithoutConstructor();

        $import = Mockery::mock(Import::class)->makePartial();
        $import->source = 'google_sheets';
        $import->sync_enabled = true;
        $import->project_id = 1;
        $import->id = 42;

        $reflection = new ReflectionClass($job);
        $property = $reflection->getProperty('import');
        $property->setAccessible(true);
        $property->setValue($job, $import);

        DB::shouldReceive('table')->never();

        $method = $reflection->getMethod('removePreviousImportProspects');
        $method->setAccessible(true);

        $method->invoke($job);
        $this->assertTrue(true);
    }

    public function test_it_coalesces_duplicate_google_sheet_sync_triggers_in_a_short_burst(): void
    {
        $import = new Import([
            'id' => 42,
            'source' => 'google_sheets',
            'sync_enabled' => true,
        ]);

        $syncer = app(GoogleSheetSyncer::class);

        $this->assertTrue($syncer->claimSyncRequest($import, 15));
        $this->assertFalse($syncer->claimSyncRequest($import, 15));
    }

    public function test_it_counts_coalesced_google_sheet_bursts_without_delaying_the_real_sync(): void
    {
        $import = new Import([
            'id' => 42,
            'source' => 'google_sheets',
            'sync_enabled' => true,
        ]);

        $syncer = app(GoogleSheetSyncer::class);

        $this->assertTrue($syncer->claimSyncRequest($import, 15));
        $this->assertFalse($syncer->claimSyncRequest($import, 15));
        $this->assertFalse($syncer->claimSyncRequest($import, 15));

        $this->assertSame(2, $syncer->consumeCoalescedBurstCount($import));
        $this->assertSame(0, $syncer->consumeCoalescedBurstCount($import));
    }

    public function test_it_uses_selected_duplicate_fields_instead_of_email_or_phone_when_scanning_import_rows(): void
    {
        $job = (new ReflectionClass(ImportProspects::class))->newInstanceWithoutConstructor();

        $setProperty = function (string $name, mixed $value) use ($job): void {
            $property = (new ReflectionClass($job))->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($job, $value);
        };

        $setProperty('existingEmails', [
            'other@example.com' => ['id' => 77, 'import_id' => 8],
        ]);
        $setProperty('existingPhones', [
            '+33100000000' => ['id' => 77, 'import_id' => 8],
        ]);
        $setProperty('existingMobiles', [
            '+33100000000' => ['id' => 77, 'import_id' => 8],
        ]);
        $setProperty('import', new Import([
            'id' => 42,
            'project_id' => 1,
            'duplicates_fields' => [12],
        ]));
        $setProperty('duplicateFieldDescriptors', [
            ['id' => 12, 'slug' => 'first_name', 'meta' => false],
        ]);
        $setProperty('existingDuplicateFieldValues', [
            'first_name' => [
                'john' => ['id' => 99, 'import_id' => 7],
            ],
        ]);

        $method = (new ReflectionClass($job))->getMethod('findExistingDuplicate');
        $method->setAccessible(true);

        $duplicate = $method->invoke($job, [
            'first_name' => 'John',
            'email' => 'other@example.com',
            'phone_number' => '01 00 00 00 00',
            'mobile_phone_number' => '0100000000',
        ]);

        $this->assertNotNull($duplicate);
        $this->assertSame(99, $duplicate['id']);
        $this->assertSame(['first_name'], $duplicate['fields']);
    }
}
