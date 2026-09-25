<?php

namespace Tests\Feature;

use App\Jobs\ImportProspects;
use App\Models\Import;
use ReflectionClass;
use Tests\TestCase;

class ImportDuplicateFieldsTest extends TestCase
{
    private function makeJob(array $props): ImportProspects
    {
        $job = (new ReflectionClass(ImportProspects::class))->newInstanceWithoutConstructor();

        $props += ['import' => (new Import())->forceFill(['id' => 42, 'project_id' => 1])];

        foreach ($props as $name => $value) {
            $property = (new ReflectionClass($job))->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($job, $value);
        }

        return $job;
    }

    private function invoke(ImportProspects $job, string $method, ...$args): mixed
    {
        $m = (new ReflectionClass($job))->getMethod($method);
        $m->setAccessible(true);

        return $m->invoke($job, ...$args);
    }

    public function test_selected_phone_field_is_compared_in_normalized_form(): void
    {
        $job = $this->makeJob([
            'duplicateFieldDescriptors' => [['id' => 1, 'slug' => 'mobile_phone_number', 'meta' => false]],
            // Existing CRM value "06 12 34 56 78", indexed normalized.
            'existingDuplicateFieldValues' => ['mobile_phone_number' => ['0612345678' => ['id' => 5, 'import_id' => null]]],
        ]);

        $this->assertSame('0612345678', $this->invoke($job, 'normalizeDuplicateComparisonValue', '06 12 34 56 78', 'mobile_phone_number'));

        $duplicate = $this->invoke($job, 'findExistingDuplicate', ['mobile_phone_number' => '0612345678']);

        $this->assertNotNull($duplicate);
        $this->assertSame(5, $duplicate['id']);
    }

    public function test_only_the_selected_fields_are_compared_never_email_or_phone(): void
    {
        $job = $this->makeJob([
            'duplicateFieldDescriptors' => [['id' => 1, 'slug' => 'first_name', 'meta' => false]],
            'existingDuplicateFieldValues' => ['first_name' => ['john' => ['id' => 5, 'import_id' => null]]],
            // Same email/phone as an existing prospect, but not selected.
            'existingEmails' => ['john@x.fr' => ['id' => 9, 'import_id' => null]],
            'existingMobiles' => ['0612345678' => ['id' => 9, 'import_id' => null]],
        ]);

        // Different first name: not a duplicate even though email and mobile match.
        $this->assertNull($this->invoke($job, 'findExistingDuplicate', [
            'first_name' => 'Paul', 'email' => 'john@x.fr', 'mobile_phone_number' => '0612345678',
        ]));

        // Same first name: duplicate, whatever the email.
        $this->assertSame(5, $this->invoke($job, 'findExistingDuplicate', ['first_name' => 'John', 'email' => 'other@x.fr'])['id']);
    }

    public function test_row_without_value_in_selected_fields_is_not_matched_on_anything_else(): void
    {
        $job = $this->makeJob([
            'duplicateFieldDescriptors' => [['id' => 1, 'slug' => 'first_name', 'meta' => false]],
            'existingDuplicateFieldValues' => [],
            'existingEmails' => ['john@x.fr' => ['id' => 9, 'import_id' => 42]],
        ]);

        $this->assertNull($this->invoke($job, 'findExistingDuplicate', ['first_name' => '', 'email' => 'john@x.fr']));
    }

    public function test_selected_phone_field_does_not_match_the_other_phone_column(): void
    {
        $job = $this->makeJob([
            'duplicateFieldDescriptors' => [['id' => 1, 'slug' => 'mobile_phone_number', 'meta' => false]],
            // Existing prospect has this number as phone_number, NOT as mobile.
            'existingDuplicateFieldValues' => [],
        ]);

        $this->assertNull($this->invoke($job, 'findExistingDuplicate', ['mobile_phone_number' => '0612345678', 'phone_number' => '0612345678']));
    }

    public function test_repeats_within_the_file_only_use_the_selected_fields(): void
    {
        $job = $this->makeJob([
            'duplicateFieldDescriptors' => [['id' => 1, 'slug' => 'first_name', 'meta' => false]],
        ]);

        // Same email twice but different first names: not repeats.
        $this->assertFalse($this->invoke($job, 'isRepeatedWithinFile', ['first_name' => 'Ann', 'email' => 'a@b.fr']));
        $this->assertFalse($this->invoke($job, 'isRepeatedWithinFile', ['first_name' => 'Bob', 'email' => 'a@b.fr']));
        // Same first name: repeat.
        $this->assertTrue($this->invoke($job, 'isRepeatedWithinFile', ['first_name' => 'Ann', 'email' => 'z@b.fr']));
        // No value in the selected field: never a repeat.
        $this->assertFalse($this->invoke($job, 'isRepeatedWithinFile', ['first_name' => null, 'email' => 'q@b.fr']));
        $this->assertFalse($this->invoke($job, 'isRepeatedWithinFile', ['first_name' => null, 'email' => 'q@b.fr']));
    }

    public function test_historical_email_phone_comparison_when_nothing_is_selected(): void
    {
        $job = $this->makeJob(['duplicateFieldDescriptors' => []]);

        $row = ['email' => 'a@b.fr'];
        $this->assertFalse($this->invoke($job, 'isRepeatedWithinFile', $row));
        $this->assertTrue($this->invoke($job, 'isRepeatedWithinFile', $row));
    }

    public function test_google_sheets_import_never_excludes_its_own_prospects_from_the_duplicate_indexes(): void
    {
        $sheet = (new Import())->forceFill(['id' => 42, 'project_id' => 1, 'source' => 'google_sheets']);
        $file = (new Import())->forceFill(['id' => 43, 'project_id' => 1, 'source' => 'file']);

        // Manual (non incremental) run of a Google Sheets import.
        $this->assertFalse($this->invoke($this->makeJob(['import' => $sheet, 'incremental' => false]), 'excludesOwnProspects'));
        $this->assertFalse($this->invoke($this->makeJob(['import' => $sheet, 'incremental' => true]), 'excludesOwnProspects'));
        // Plain file import: own rows are wiped and recreated.
        $this->assertTrue($this->invoke($this->makeJob(['import' => $file, 'incremental' => false]), 'excludesOwnProspects'));
    }

    public function test_never_imported_google_sheet_is_not_auto_synced(): void
    {
        $syncer = app(\App\Services\Import\GoogleSheetSyncer::class);

        $fresh = (new Import())->forceFill(['sync_enabled' => true, 'processed_at' => null, 'last_synced_at' => null]);
        $this->assertFalse($syncer->hasBeenImportedOnce($fresh));
        $this->assertFalse($syncer->isDue($fresh));

        $imported = (new Import())->forceFill(['processed_at' => now()->subHour()->toDateTimeString(), 'sync_interval_minutes' => 1]);
        $this->assertTrue($syncer->hasBeenImportedOnce($imported));
        $this->assertTrue($syncer->isDue($imported));
    }
}
