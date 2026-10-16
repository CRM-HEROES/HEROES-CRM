<?php

namespace Tests\Feature;

use App\Exports\ProspectExport;
use App\Exports\ProspectDownload;
use App\Http\Controllers\API\Project\ExportController;
use App\Jobs\ExportProspects;
use App\Jobs\PrepareProspectDownload;
use App\Models\Export;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class ProspectExportDeliveryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs((new User())->forceFill(['id' => 1, 'email' => 'creator@example.com']));
        Bus::fake([ExportProspects::class, PrepareProspectDownload::class]);
        Storage::fake('exports');
    }

    public function test_email_mode_queues_filtered_prospects(): void
    {
        $this->mock(ProspectDownload::class)->shouldNotReceive('download');

        $response = $this->createExport([
            'mode' => 'email',
            'email' => 'recipient+export@example.com',
            'filters' => json_encode(['keyword' => 'A&B']),
            'count' => 1000,
        ], true);

        $this->assertSame(202, $response->getStatusCode());
        Bus::assertDispatched(ExportProspects::class, fn ($job) => $job->queue === 'imports');
    }

    public function test_download_mode_exports_all_rows_without_an_email_or_filters(): void
    {
        $response = $this->createExport(['mode' => 'download', 'count' => 0], true);

        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('/api/project/test/export/123/status', $response->getData(true)['status_url']);
        $this->assertSame('/api/project/test/export/123/download', $response->getData(true)['download_url']);
        Bus::assertDispatched(PrepareProspectDownload::class, fn ($job) => $job->queue === 'imports' && $job->export->count === 0);
        Storage::disk('exports')->assertExists('downloads/123.json');
        Bus::assertNotDispatched(ExportProspects::class);
    }

    public function test_download_mode_honors_the_requested_limit_and_filters(): void
    {
        $filters = ['ids' => [10, 20]];
        $this->createExport([
            'mode' => 'download',
            'count' => 1000,
            'filters' => json_encode($filters),
        ], true);

        Bus::assertDispatched(PrepareProspectDownload::class, fn ($job) => $job->export->count === 1000 && $job->export->filters === $filters);
        Bus::assertNotDispatched(ExportProspects::class);
    }

    public function test_existing_callers_without_a_mode_keep_downloading_filtered_exports(): void
    {
        $this->expectDownload(0, ['ids' => [10]]);

        $this->createExport(['filters' => json_encode(['ids' => [10]])], true);

        Bus::assertNotDispatched(ExportProspects::class);
    }

    public function test_email_mode_requires_a_valid_recipient_before_creating_an_export(): void
    {
        $this->expectException(ValidationException::class);

        $this->createExport(['mode' => 'email', 'email' => 'invalid'], false);
    }

    public function test_unknown_modes_are_rejected_before_creating_an_export(): void
    {
        $this->expectException(ValidationException::class);

        $this->createExport(['mode' => 'unknown'], false);
    }

    protected function createExport(array $params, bool $shouldCreate)
    {
        $project = Mockery::mock(Project::class)->makePartial();
        $project->id = 7;
        $project->slug = 'test';
        $project->name = 'Prospects';

        if ($shouldCreate) {
            $relation = Mockery::mock(HasMany::class);
            $project->shouldReceive('exports')->once()->andReturn($relation);
            $relation->shouldReceive('create')->once()->with(Mockery::on(function ($attributes) use ($params) {
                return $attributes['count'] === ($params['count'] ?? 0)
                    && $attributes['filters'] == json_decode($params['filters'] ?? '{}')
                    && $attributes['email'] === ($params['email'] ?? 'creator@example.com');
            }))->andReturnUsing(function ($attributes) use ($project) {
                return (new Export($attributes))->forceFill(['id' => 123, 'project_id' => 7])->setRelation('project', $project);
            });
        } else {
            $project->shouldNotReceive('exports');
        }

        $controller = Mockery::mock(ExportController::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $controller->shouldReceive('getFields')->andReturn(['users']);
        $request = Request::create('/api/project/test/export/create', 'GET', $params, [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        return $controller->create($request, $project);
    }

    protected function expectDownload(int $limit, array $filters = []): void
    {
        $response = Mockery::mock(\Symfony\Component\HttpFoundation\BinaryFileResponse::class);
        $response->shouldReceive('getContent')->andReturn('xlsx');

        $this->mock(ProspectDownload::class)->shouldReceive('download')->once()->with(Mockery::on(function ($export) use ($limit, $filters) {
            if (!$export instanceof ProspectExport) {
                return false;
            }

            $reflection = new \ReflectionClass($export);
            $model = $reflection->getProperty('export')->getValue($export);

            return $reflection->getProperty('limit')->getValue($export) === $limit
                && (array) $model->filters === $filters;
        }), 'Prospects.xlsx')->andReturn($response);
    }
}
