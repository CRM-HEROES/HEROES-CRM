<?php

namespace Tests\Feature;

use App\Jobs\ImportProspects;
use App\Models\Import;
use Illuminate\Support\Facades\Storage;
use ReflectionClass;
use Tests\TestCase;

class ImportRowFingerprintTest extends TestCase
{
    private function makeJob(array $mapping = ['a' => 1]): ImportProspects
    {
        $job = (new ReflectionClass(ImportProspects::class))->newInstanceWithoutConstructor();

        $set = function (string $name, mixed $value) use ($job): void {
            $property = (new ReflectionClass($job))->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($job, $value);
        };

        $set('import', new Import(['id' => 7, 'selected_sheets' => null]));
        $set('mapping', $mapping);

        return $job;
    }

    private function invoke(ImportProspects $job, string $method, ...$args): mixed
    {
        $m = (new ReflectionClass($job))->getMethod($method);
        $m->setAccessible(true);

        return $m->invoke($job, ...$args);
    }

    public function test_row_fingerprint_is_stable_and_content_sensitive(): void
    {
        $job = $this->makeJob();

        $a = $this->invoke($job, 'fingerprintRow', 'Sheet1', ['john@x.fr', '0612345678']);

        $this->assertSame($a, $this->invoke($job, 'fingerprintRow', 'Sheet1', ['john@x.fr', '0612345678']));
        $this->assertNotSame($a, $this->invoke($job, 'fingerprintRow', 'Sheet1', ['john@x.fr', '0612345679']));
        $this->assertNotSame($a, $this->invoke($job, 'fingerprintRow', 'Sheet2', ['john@x.fr', '0612345678']));
    }

    public function test_fingerprints_round_trip_and_are_invalidated_when_the_mapping_changes(): void
    {
        Storage::fake('imports');

        $job = $this->makeJob(['a' => 1]);
        $this->assertSame([], $this->invoke($job, 'loadRowFingerprints'));

        $this->invoke($job, 'saveRowFingerprints', ['aaaa', 'bbbb']);
        $this->assertSame(['aaaa' => true, 'bbbb' => true], $this->invoke($job, 'loadRowFingerprints'));

        $changed = $this->makeJob(['a' => 2]);
        $this->assertSame([], $this->invoke($changed, 'loadRowFingerprints'));
    }

    public function test_corrupt_fingerprint_file_means_process_everything(): void
    {
        Storage::fake('imports');
        Storage::disk('imports')->put('.sync-state/import-7.json', '{not json');

        $this->assertSame([], $this->invoke($this->makeJob(), 'loadRowFingerprints'));
    }
}
