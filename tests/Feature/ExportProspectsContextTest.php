<?php

namespace Tests\Feature;

use App\Exports\ProspectExport;
use App\Jobs\ExportProspects;
use App\Models\Export;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ExportProspectsContextTest extends TestCase
{
    /**
     * @dataProvider contextCases
     */
    public function test_email_export_uses_creator_context_and_restores_worker_context(bool $authenticated, bool $fails): void
    {
        $previousUser = $authenticated ? (new User())->forceFill(['id' => 19]) : null;
        if ($previousUser) {
            Auth::setUser($previousUser);
        } else {
            Auth::forgetUser();
        }
        setPermissionsTeamId(23);
        request()->replace(['worker-input' => 'retained', 'project' => 'previous-project']);
        $previousInput = request()->all();

        $creator = (new User())->forceFill(['id' => 1]);
        $project = (new Project())->forceFill(['id' => 7]);
        $export = (new Export())->forceFill([
            'id' => 123,
            'creator_id' => 1,
            'project_id' => 7,
            'count' => 0,
            'fields' => [],
        ])->setRelation('creator', $creator)->setRelation('project', $project);

        Excel::shouldReceive('store')->once()->with(
            Mockery::type(ProspectExport::class), '123-1.xlsx', 'exports'
        )->andReturnUsing(function () use ($creator, $project, $fails) {
            $this->assertSame($creator, Auth::user());
            $this->assertSame($project, request()->project);
            $this->assertSame(7, getPermissionsTeamId());

            if ($fails) {
                throw new RuntimeException('Export write failed');
            }

            return true;
        });

        $job = new class($export) extends ExportProspects {
            public bool $notified = false;

            protected function notifyExportFinished()
            {
                $this->notified = true;
            }
        };

        try {
            $job->handle();
            $this->assertFalse($fails, 'The write failure must propagate to the queue worker.');
        } catch (RuntimeException $exception) {
            $this->assertTrue($fails);
            $this->assertSame('Export write failed', $exception->getMessage());
        }

        $this->assertSame(!$fails, $job->notified);
        $this->assertSame($previousUser, Auth::user());
        $this->assertSame(23, getPermissionsTeamId());
        $this->assertSame($previousInput, request()->all());
    }

    public static function contextCases(): array
    {
        return [
            'authenticated worker, success' => [true, false],
            'authenticated worker, failure' => [true, true],
            'unauthenticated worker, success' => [false, false],
            'unauthenticated worker, failure' => [false, true],
        ];
    }
}
