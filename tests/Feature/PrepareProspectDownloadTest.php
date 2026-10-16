<?php

namespace Tests\Feature;

use App\Exports\ProspectDownload;
use App\Http\Controllers\API\Project\ExportController;
use App\Jobs\PrepareProspectDownload;
use App\Models\Export;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PrepareProspectDownloadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('exports');
        $this->actingAs((new User())->forceFill(['id' => 1]));
    }

    public function test_file_is_prepared_then_available_for_download(): void
    {
        $export = $this->export();
        $download = new ProspectDownload();
        $download->setStatus($export, 'processing');
        $download->prepare($export);

        $this->assertSame('ready', $download->status($export)['status']);
        $this->assertSame(0, $download->status($export)['rows']);
        Storage::disk('exports')->assertExists('downloads/123.xlsx');
        $response = (new ExportController())->download($export->project, $export);
        $this->assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));

        // Completed downloads remain available if the browser needs to retry.
        $download->delete($export);
        Storage::disk('exports')->assertMissing('downloads/123.xlsx');
        $this->assertSame('missing', $download->status($export)['status']);
    }

    public function test_status_and_file_are_restricted_to_the_creator_and_project(): void
    {
        $export = $this->export();
        $controller = new ExportController();
        $download = new ProspectDownload();
        $download->setStatus($export, 'queued');
        $this->assertSame('queued', $controller->status($export->project, $export)['status']);

        foreach (['status', 'download'] as $method) {
            foreach ([[2, 7, 403], [1, 8, 404]] as [$userId, $projectId, $code]) {
                $this->actingAs((new User())->forceFill(['id' => $userId]));
                $project = (new Project())->forceFill(['id' => $projectId]);
                try {
                    $controller->{$method}($project, $export);
                    $this->fail('Access should have been refused.');
                } catch (HttpException $exception) {
                    $this->assertSame($code, $exception->getStatusCode());
                }
            }
        }
    }

    public function test_unfinished_files_cannot_be_downloaded(): void
    {
        $export = $this->export();
        $download = new ProspectDownload();
        $download->setStatus($export, 'processing');
        Storage::disk('exports')->put('downloads/123.xlsx', 'incomplete');

        $this->expectException(HttpException::class);
        $download->preparedFile($export);
    }

    public function test_job_restores_authentication_project_and_team_context_after_failure(): void
    {
        $export = $this->export();
        $download = Mockery::mock(ProspectDownload::class)->makePartial();
        $download->shouldReceive('prepare')->once()->with($export)->andReturnUsing(function () use ($export) {
            $this->assertSame($export->creator, Auth::user());
            $this->assertSame($export->project, request()->project);
            $this->assertSame(7, getPermissionsTeamId());
            throw new RuntimeException('disk unavailable');
        });
        $user = Auth::user();
        $team = getPermissionsTeamId();
        $input = request()->all();

        try {
            (new PrepareProspectDownload($export))->handle($download);
            $this->fail('The disk error should propagate to the queue worker.');
        } catch (RuntimeException $exception) {
            $this->assertSame('disk unavailable', $exception->getMessage());
        }

        $this->assertSame($user, Auth::user());
        $this->assertSame($team, getPermissionsTeamId());
        $this->assertSame($input, request()->all());

        (new PrepareProspectDownload($export))->failed(new RuntimeException('disk unavailable'));
        $this->assertSame('failed', (new ProspectDownload())->status($export)['status']);
    }

    protected function export(): Export
    {
        $project = Mockery::mock(Project::class)->makePartial();
        $project->forceFill(['id' => 7, 'slug' => 'test', 'name' => 'Prospects']);
        $project->shouldReceive('fields->where->where->first')->andReturn(null);

        // No rows: exercise the real XLSX writer without accessing the database.
        $query = Mockery::mock();
        $query->shouldReceive('when', 'orderBy', 'filter')->andReturnSelf();
        $query->shouldReceive('get')->andReturn(collect());
        $project->shouldReceive('prospects')->andReturn($query);

        return (new Export())->forceFill([
            'id' => 123, 'project_id' => 7, 'creator_id' => 1,
            'count' => 0, 'fields' => [], 'filters' => [],
            'created_at' => now(), 'sort_by' => 'id', 'sort_order' => 'desc',
        ])->setRelation('project', $project)
            ->setRelation('creator', (new User())->forceFill(['id' => 1]));
    }
}
