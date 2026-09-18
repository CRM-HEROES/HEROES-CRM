<?php

namespace Tests\Feature;

use App\Jobs\GoogleSheetSyncRequest;
use App\Jobs\ImportProspects;
use App\Models\Import;
use App\Services\Import\GoogleSheetSyncer;
use Illuminate\Support\Facades\Bus;
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
}
