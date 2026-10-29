<?php

namespace Tests\Feature;

use App\Exports\ProspectExport;
use App\Exports\ProspectDownload;
use App\Http\Controllers\API\Project\ExportController;
use App\Jobs\ExportProspects;
use App\Jobs\PrepareProspectDownload;
use App\Jobs\PrepareProspectSheet;
use App\Models\Export;
use App\Models\GoogleAccount;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ProspectExportDeliveryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs((new User())->forceFill(['id' => 1, 'email' => 'creator@example.com']));
        Gate::before(fn ($user, $ability) => $ability === 'prospectExport' ? config('testing.allow_prospect_export', true) : null);
        Bus::fake([ExportProspects::class, PrepareProspectDownload::class, PrepareProspectSheet::class]);
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

    public function test_sheet_mode_queues_selected_columns_filters_and_limit(): void
    {
        $account = (new GoogleAccount())->forceFill(['id' => 45, 'project_id' => 7, 'for' => 'drive']);
        $filters = ['ids' => [10, 20]];
        $fields = ['firstname', 'meta->custom-column'];
        $response = $this->createExport([
            'mode' => 'sheet',
            'sheet_name' => 'Prospects octobre',
            'count' => 1000,
            'fields' => implode(',', $fields),
            'filters' => json_encode($filters),
        ], true, $account);

        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('/api/project/test/export/123/status', $response->getData(true)['status_url']);
        Bus::assertDispatched(PrepareProspectSheet::class, function ($job) use ($filters, $fields) {
            return $job->queue === 'imports'
                && $job->export->count === 1000
                && $job->export->filters === $filters
                && $job->export->fields === $fields;
        });
        Bus::assertNotDispatched(ExportProspects::class);
        Bus::assertNotDispatched(PrepareProspectDownload::class);
        $this->assertSame('sheet', (new ProspectDownload())->status((new Export())->forceFill(['id' => 123]))['mode']);
    }

    public function test_sheet_mode_requires_a_connected_drive_account_before_creating_an_export(): void
    {
        $this->expectException(ValidationException::class);

        $this->createExport(['mode' => 'sheet'], false);
    }

    public function test_sheet_name_is_limited_before_creating_an_export(): void
    {
        $this->expectException(ValidationException::class);

        $this->createExport(['mode' => 'sheet', 'sheet_name' => str_repeat('x', 151)], false);
    }

    public function test_sheet_mode_checks_export_permission_before_creating_an_export(): void
    {
        config(['testing.allow_prospect_export' => false]);
        $this->expectException(AuthorizationException::class);

        $this->createExport(['mode' => 'sheet'], false);
    }

    public function test_sheet_configuration_requires_export_permission(): void
    {
        config(['testing.allow_prospect_export' => false]);
        $this->expectException(AuthorizationException::class);

        (new ExportController())->sheetConfiguration(new Project());
    }

    public function test_sheet_configuration_only_returns_the_connection_name(): void
    {
        $account = (new GoogleAccount())->forceFill([
            'id' => 45, 'name' => 'sheets@example.com',
            'token' => ['access_token' => 'private-token', 'refresh_token' => 'private-refresh'],
        ]);
        $controller = Mockery::mock(ExportController::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $controller->shouldReceive('sheetAccount')->once()->andReturn($account);

        $this->assertSame([
            'connected' => true, 'account_name' => 'sheets@example.com',
        ], $controller->sheetConfiguration(new Project()));
    }

    public function test_sheet_configuration_reports_a_missing_connection(): void
    {
        $controller = Mockery::mock(ExportController::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $controller->shouldReceive('sheetAccount')->once()->andReturn(null);

        $this->assertSame([
            'connected' => false, 'account_name' => null,
        ], $controller->sheetConfiguration(new Project()));
    }

    public function test_ready_sheet_opens_its_google_url_from_the_export_history(): void
    {
        $project = (new Project())->forceFill(['id' => 7]);
        $export = (new Export())->forceFill(['id' => 123, 'project_id' => 7, 'creator_id' => 1]);
        $url = 'https://docs.google.com/spreadsheets/d/sheet-123/edit';
        (new ProspectDownload())->setStatus($export, 'ready', [
            'mode' => 'sheet', 'sheet_id' => 'sheet-123', 'sheet_url' => 'https://example.com/untrusted',
        ]);

        $this->assertSame($url, (new ExportController())->show($project, $export)->getTargetUrl());
    }

    public function test_sheet_history_redirect_is_restricted_to_its_creator_and_project(): void
    {
        $export = (new Export())->forceFill(['id' => 123, 'project_id' => 7, 'creator_id' => 1]);
        (new ProspectDownload())->setStatus($export, 'ready', [
            'mode' => 'sheet', 'sheet_id' => 'sheet-123',
            'sheet_url' => 'https://docs.google.com/spreadsheets/d/sheet-123/edit',
        ]);

        foreach ([[2, 7, 403], [1, 8, 404]] as [$userId, $projectId, $code]) {
            $this->actingAs((new User())->forceFill(['id' => $userId]));

            try {
                (new ExportController())->show((new Project())->forceFill(['id' => $projectId]), $export);
                $this->fail('The sheet should only be opened by its creator in the same project.');
            } catch (HttpException $exception) {
                $this->assertSame($code, $exception->getStatusCode());
            }
        }
    }

    protected function createExport(array $params, bool $shouldCreate, ?GoogleAccount $account = null)
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
        $controller->shouldReceive('getFields')->andReturn(isset($params['fields']) ? explode(',', $params['fields']) : ['users']);
        $controller->shouldReceive('sheetAccount')->andReturn($account);
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
