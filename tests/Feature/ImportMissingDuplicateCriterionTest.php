<?php

namespace Tests\Feature;

use App\Jobs\ImportProspects;
use App\Models\Import;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The field chosen in "MAJ" only detects duplicates when the lead has a value
 * for it; a lead without that value must still be imported.
 */
class ImportMissingDuplicateCriterionTest extends TestCase
{
    use DatabaseTransactions;

    protected int $projectId;
    protected array $fieldIds = [];
    protected array $files = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->projectId = DB::table('projects')->insertGetId([
            'name' => 'maj test',
            'slug' => 'maj-test-' . bin2hex(random_bytes(4)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (['email' => 0, 'phone_number' => 0, 'ref' => 1] as $slug => $meta) {
            $this->fieldIds[$slug] = DB::table('fields')->insertGetId([
                'project_id' => $this->projectId, 'name' => $slug, 'slug' => $slug,
                'for' => 'prospect', 'meta' => $meta, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }

        parent::tearDown();
    }

    protected function runImport(array $rows, array $majSlugs): array
    {
        $headers = ['email', 'first_name', 'phone_number', 'ref', 'job_title'];
        $name = 'majtest-' . bin2hex(random_bytes(5)) . '.csv';
        $path = storage_path('app/imports/' . $name);
        $this->files[] = $path;

        $fh = fopen($path, 'w');
        fputcsv($fh, $headers);
        foreach ($rows as $row) {
            fputcsv($fh, $row);
        }
        fclose($fh);

        $id = DB::table('imports')->insertGetId([
            'project_id' => $this->projectId,
            'name' => 'maj',
            'path' => $name,
            'headers' => json_encode($headers),
            'mapping' => json_encode(['email', 'first_name', 'phone_number', 'meta->ref', 'job_title']),
            'duplicates_fields' => $majSlugs ? json_encode(array_map(fn ($s) => $this->fieldIds[$s], $majSlugs)) : null,
            'is_processing' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $import = Import::withoutGlobalScopes()->find($id);
        (new ImportProspects($import))->handle();

        return DB::table('prospects')->where('import_id', $id)->get()->all();
    }

    protected function existing(array $attrs): int
    {
        return DB::table('prospects')->insertGetId($attrs + [
            'project_id' => $this->projectId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function names(array $prospects): array
    {
        $names = array_map(fn ($p) => (string) $p->first_name, $prospects);
        sort($names);

        return $names;
    }

    public function test_lead_without_email_is_imported_when_maj_is_email(): void
    {
        $result = $this->runImport([
            ['a@x.com', 'WithEmail', '', ''],
            ['', 'NameOnly', '', ''],
            ['', '', '0612345678', ''],
        ], ['email']);

        $this->assertCount(3, $result);
    }

    public function test_lead_with_only_a_custom_field_is_imported_when_maj_is_email(): void
    {
        $result = $this->runImport([['', '', '', 'REF-1']], ['email']);

        $this->assertCount(1, $result);
        $this->assertSame('REF-1', json_decode($result[0]->meta, true)['ref']);
    }

    public function test_lead_with_email_existing_in_database_is_still_skipped_and_untouched(): void
    {
        $id = $this->existing(['email' => 'dup@x.com', 'first_name' => 'Original']);

        $result = $this->runImport([
            ['DUP@x.com', 'Incoming', '', ''],
            ['', 'NoEmail', '', ''],
        ], ['email']);

        $this->assertSame(['NoEmail'], $this->names($result));
        $this->assertSame('Original', DB::table('prospects')->find($id)->first_name);
    }

    public function test_email_repeated_in_file_is_kept_once_but_emailless_rows_are_all_kept(): void
    {
        $result = $this->runImport([
            ['same@x.com', 'First', '', ''],
            ['same@x.com', 'Second', '', ''],
            ['', 'Anon1', '', ''],
            ['', 'Anon2', '', ''],
            ['', 'Anon2', '', ''],
        ], ['email']);

        $this->assertSame(['Anon1', 'Anon2', 'Anon2', 'First'], $this->names($result));
    }

    public function test_emailless_lead_with_phone_matching_existing_is_imported_when_maj_is_email_only(): void
    {
        $this->existing(['email' => 'e@x.com', 'phone_number' => '+33612345678']);

        $result = $this->runImport([['', 'SamePhone', '0612345678', '']], ['email']);

        $this->assertSame(['SamePhone'], $this->names($result));
    }

    public function test_composite_maj_email_and_phone_with_partial_values(): void
    {
        $this->existing(['email' => 'e@x.com', 'phone_number' => '+33612345678']);

        $result = $this->runImport([
            ['e@x.com', 'EmailOnly', '', ''],
            ['e@x.com', 'Both', '0612345678', ''],
            ['', 'PhoneOnly', '0612345678', ''],
            ['', 'Nothing', '', ''],
        ], ['email', 'phone_number']);

        // "Both" matches every criterion of the existing lead: duplicate.
        $this->assertSame(['EmailOnly', 'Nothing', 'PhoneOnly'], $this->names($result));
    }

    public function test_lead_without_the_selected_criterion_is_imported_whatever_the_criterion(): void
    {
        $this->existing(['job_title' => 'Boss', 'first_name' => 'Old']);
        $this->fieldIds['job_title'] = DB::table('fields')->insertGetId([
            'project_id' => $this->projectId, 'name' => 'job_title', 'slug' => 'job_title',
            'for' => 'prospect', 'meta' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $result = $this->runImport([
            ['a@x.com', 'Dup', '', '', 'Boss'],
            ['b@x.com', 'NoJob', '', '', ''],
            ['', '', '0612345678', '', ''],
            ['', '', '', 'OnlyRef', ''],
            ['', '', '', '', ''],
        ], ['job_title']);

        $this->assertCount(3, $result);
        $this->assertContains('NoJob', $this->names($result));
    }

    public function test_maj_on_mobile_phone_number_detects_duplicates_and_keeps_leads_without_phone(): void
    {
        $this->existing(['mobile_phone_number' => '+33612345678', 'first_name' => 'Old']);
        $this->fieldIds['mobile_phone_number'] = DB::table('fields')->insertGetId([
            'project_id' => $this->projectId, 'name' => 'mobile', 'slug' => 'mobile_phone_number',
            'for' => 'prospect', 'meta' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);

        // The "phone_number" column is automatically mapped to mobile_phone_number.
        $result = $this->runImport([
            ['a@x.com', 'Dup', '0612345678', ''],
            ['b@x.com', 'NoPhone', '', ''],
        ], ['mobile_phone_number']);

        $this->assertSame(['NoPhone'], $this->names($result));
    }

    public function test_maj_on_a_custom_field(): void
    {
        $this->existing(['first_name' => 'Old', 'meta' => json_encode(['ref' => 'R1'])]);

        $result = $this->runImport([
            ['', 'Dup', '', 'R1'],
            ['', 'NoRef', '', ''],
            ['a@x.com', 'Fresh', '', 'R2'],
        ], ['ref']);

        $this->assertSame(['Fresh', 'NoRef'], $this->names($result));
    }

    public function test_completely_empty_and_suspicious_rows_are_still_skipped(): void
    {
        $result = $this->runImport([
            ['', '', '', ''],
            ['not-an-email', 'Shifted', '', ''],
            ['', 'Ok', '', ''],
        ], ['email']);

        $this->assertSame(['Ok'], $this->names($result));
    }

    public function test_without_maj_criterion_behaviour_is_unchanged(): void
    {
        $this->existing(['email' => 'dup@x.com']);

        $result = $this->runImport([
            ['dup@x.com', 'Dup', '', ''],
            ['', '', '', 'ONLYREF'],
            ['', 'Named', '', ''],
            ['n@x.com', 'Mail', '', ''],
        ], []);

        // The meta-only row is still skipped when no MAJ field is chosen.
        $this->assertSame(['Mail', 'Named'], $this->names($result));
    }

    public function test_sync_rows_imports_emailless_lead_with_maj_email(): void
    {
        $headers = ['email', 'first_name', 'phone_number', 'ref'];
        $import = Import::withoutGlobalScopes()->find(DB::table('imports')->insertGetId([
            'project_id' => $this->projectId,
            'name' => 'sync',
            'headers' => json_encode($headers),
            'mapping' => json_encode(['email', 'first_name', 'phone_number', 'meta->ref']),
            'duplicates_fields' => json_encode([$this->fieldIds['email']]),
            'created_at' => now(),
            'updated_at' => now(),
        ]));
        $this->existing(['email' => 'dup@x.com']);

        $res = (new ImportProspects($import))->syncRows([
            ['key' => 'a', 'headers' => $headers, 'values' => ['dup@x.com', 'Dup', '', '']],
            ['key' => 'b', 'headers' => $headers, 'values' => ['', 'NoEmail', '', '']],
            ['key' => 'c', 'headers' => $headers, 'values' => ['', '', '', 'REF']],
            ['key' => 'd', 'headers' => $headers, 'values' => ['', '', '', '']],
        ]);

        $this->assertSame('skipped', $res['a']['status']);
        $this->assertSame('created', $res['b']['status']);
        $this->assertSame('created', $res['c']['status']);
        $this->assertSame('skipped', $res['d']['status']);
    }
}
