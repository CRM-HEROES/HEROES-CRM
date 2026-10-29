<?php

namespace Tests\Feature;

use App\Exports\ProspectDownload;
use App\Exports\ProspectExport;
use App\Exports\ProspectSheet;
use App\Jobs\PrepareProspectSheet;
use App\Models\Export;
use App\Models\GoogleAccount;
use App\Models\Project;
use App\Models\User;
use App\Services\Google\Drive as GoogleDrive;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ProspectSheetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('exports');
        $this->actingAs((new User())->forceFill(['id' => 99]));
        config(['google.client_id' => 'client-id', 'google.client_secret' => 'client-secret']);
    }

    public function test_sheet_is_created_in_bounded_batches_and_preserves_literal_values(): void
    {
        $export = $this->export();
        $rows = $this->rows($export, 620);
        $history = [];
        $sheet = $this->service($rows, [
            $this->jsonResponse(['spreadsheetId' => 'sheet-123', 'spreadsheetUrl' => 'https://example.com/untrusted']),
            $this->jsonResponse([]),
            $this->jsonResponse([]),
        ], $history);

        $sheet->prepare($export, $this->account(), 'Prospects octobre');

        $this->assertCount(3, $history);
        $create = json_decode((string) $history[0]['request']->getBody(), true);
        $this->assertSame('Prospects octobre', $create['properties']['title']);
        $batches = array_map(fn ($call) => json_decode((string) $call['request']->getBody(), true), array_slice($history, 1));
        $updates = [];
        foreach ($batches as $batch) {
            foreach ($batch['requests'] as $request) {
                if (isset($request['updateCells'])) {
                    $updates[] = $request['updateCells'];
                }
            }
        }

        $this->assertCount(2, $updates);
        $this->assertSame([0, 500], array_column(array_column($updates, 'start'), 'rowIndex'));
        $this->assertSame([500, 121], array_map(fn ($update) => count($update['rows']), $updates));
        $this->assertSame('Identifier', $updates[0]['rows'][0]['values'][0]['userEnteredValue']['stringValue']);
        $cells = $updates[0]['rows'][1]['values'];
        $this->assertSame(['stringValue' => '00000'], $cells[0]['userEnteredValue']);
        $this->assertSame(['stringValue' => '=IMPORTXML("https://example.com","//title")'], $cells[1]['userEnteredValue']);
        $this->assertSame(['stringValue' => '+33123456789'], $cells[2]['userEnteredValue']);
        $this->assertSame(['boolValue' => true], $cells[3]['userEnteredValue']);
        $this->assertSame(['numberValue' => 1.5], $cells[4]['userEnteredValue']);
        $this->assertSame(['stringValue' => '2026-10-29 12:34:56'], $cells[5]['userEnteredValue']);
        $this->assertSame(['stringValue' => '{"city":"Paris"}'], $cells[6]['userEnteredValue']);
        $this->assertSame('00619', $updates[1]['rows'][120]['values'][0]['userEnteredValue']['stringValue']);
        $status = (new ProspectDownload())->status($export);
        $this->assertSame('ready', $status['status']);
        $this->assertSame('sheet', $status['mode']);
        $this->assertSame(620, $status['rows']);
        $this->assertSame('sheet-123', $status['sheet_id']);
        $this->assertSame('https://docs.google.com/spreadsheets/d/sheet-123/edit', $status['sheet_url']);
    }

    public function test_expired_token_is_refreshed_and_saved_with_its_refresh_token(): void
    {
        $export = $this->export();
        $history = [];
        $sheet = $this->service($this->rows($export, 0), [
            $this->jsonResponse(['access_token' => 'renewed-token', 'expires_in' => 3600, 'token_type' => 'Bearer']),
            $this->jsonResponse(['spreadsheetId' => 'sheet-123']),
            $this->jsonResponse([]),
        ], $history);
        $account = Mockery::mock(GoogleAccount::class)->makePartial();
        $account->forceFill([
            'id' => 45, 'project_id' => 7, 'for' => 'drive',
            'token' => ['access_token' => 'expired-token', 'refresh_token' => 'retained-refresh', 'expires_in' => 1, 'created' => 1],
        ]);
        $account->shouldReceive('update')->once()->with(Mockery::on(function ($attributes) {
            return $attributes['token']['access_token'] === 'renewed-token'
                && $attributes['token']['refresh_token'] === 'retained-refresh';
        }))->andReturn(true);

        $sheet->prepare($export, $account, 'Prospects');

        $this->assertCount(3, $history);
        $this->assertStringContainsString('oauth2.googleapis.com', (string) $history[0]['request']->getUri());
        parse_str((string) $history[0]['request']->getBody(), $refreshRequest);
        $this->assertSame('retained-refresh', $refreshRequest['refresh_token']);
        $this->assertStringContainsString('sheets.googleapis.com', (string) $history[1]['request']->getUri());
        $this->assertSame('ready', (new ProspectDownload())->status($export)['status']);
    }

    public function test_revoked_refresh_token_does_not_create_a_sheet_or_overwrite_credentials(): void
    {
        $export = $this->export();
        $history = [];
        $sheet = $this->service($this->rows($export, 0), [
            $this->jsonResponse(['error' => 'invalid_grant', 'error_description' => 'Token revoked'], 400),
        ], $history);
        $account = Mockery::mock(GoogleAccount::class)->makePartial();
        $account->forceFill([
            'id' => 45, 'project_id' => 7, 'for' => 'drive',
            'token' => ['access_token' => 'expired-token', 'refresh_token' => 'revoked-refresh', 'expires_in' => 1, 'created' => 1],
        ]);
        $account->shouldNotReceive('update');

        try {
            $sheet->prepare($export, $account, 'Prospects');
            $this->fail('Revoked credentials must stop the export.');
        } catch (RuntimeException $exception) {
            $this->assertNotSame('', $exception->getMessage());
        }

        $this->assertCount(1, $history);
        $this->assertNotSame('ready', (new ProspectDownload())->status($export)['status']);
    }

    public function test_sheet_api_errors_do_not_mark_partial_sheets_as_ready(): void
    {
        $export = $this->export();
        $history = [];
        $sheet = $this->service($this->rows($export, 1), [
            $this->jsonResponse(['spreadsheetId' => 'sheet-123']),
            $this->jsonResponse(['error' => ['code' => 403, 'message' => 'Permission denied']], 403),
        ], $history);

        try {
            $sheet->prepare($export, $this->account(), 'Prospects');
            $this->fail('The API error must propagate to the queue worker.');
        } catch (\Google\Service\Exception $exception) {
            $this->assertSame(403, $exception->getCode());
        }

        $this->assertCount(2, $history);
        $this->assertNotSame('ready', (new ProspectDownload())->status($export)['status']);
    }

    public function test_retry_reuses_the_existing_sheet_and_overwrites_the_same_cells(): void
    {
        $export = $this->export();
        (new ProspectDownload())->setStatus($export, 'failed', [
            'mode' => 'sheet', 'sheet_id' => 'existing-sheet',
            'sheet_url' => 'https://example.com/untrusted',
        ]);
        $history = [];
        $sheet = $this->service($this->rows($export, 1), [$this->jsonResponse([])], $history);

        $sheet->prepare($export, $this->account(), 'Prospects');

        $this->assertCount(1, $history);
        $this->assertSame('/v4/spreadsheets/existing-sheet:batchUpdate', $history[0]['request']->getUri()->getPath());
        $status = (new ProspectDownload())->status($export);
        $this->assertSame('ready', $status['status']);
        $this->assertSame('https://docs.google.com/spreadsheets/d/existing-sheet/edit', $status['sheet_url']);
    }

    public function test_disconnected_or_foreign_accounts_are_rejected_before_calling_google(): void
    {
        $export = $this->export();
        $history = [];
        $sheet = $this->service($this->rows($export, 0), [], $history);

        foreach ([['project_id' => 8], ['for' => 'calendar'], ['deleted_at' => now()]] as $attributes) {
            $account = $this->account()->forceFill($attributes);
            try {
                $sheet->prepare($export, $account, 'Prospects');
                $this->fail('Only the project connected Drive account may be used.');
            } catch (RuntimeException $exception) {
                $this->assertSame(ProspectSheet::CONNECTION_MESSAGE, $exception->getMessage());
            }
        }

        $this->assertSame([], $history);
    }

    public function test_job_checks_current_export_permission_before_sending_data_to_google(): void
    {
        $export = $this->export();
        $creator = Mockery::mock(User::class)->makePartial();
        $creator->forceFill(['id' => 1]);
        $creator->shouldReceive('can')->with('prospectExport', $export->project)->once()->andReturn(false);
        $export->setRelation('creator', $creator);
        $sheet = Mockery::mock(ProspectSheet::class);
        $sheet->shouldNotReceive('prepare');
        $previousUser = Auth::user();

        try {
            (new PrepareProspectSheet($export, $this->account(), 'Prospects'))->handle($sheet);
            $this->fail('Revoked export permission must stop the queued job.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('exporter', $exception->getMessage());
        }

        $this->assertSame($previousUser, Auth::user());
    }

    public function test_job_restores_authentication_project_and_team_context_after_failure(): void
    {
        $export = $this->export();
        $account = $this->account();
        $sheet = Mockery::mock(ProspectSheet::class);
        $sheet->shouldReceive('prepare')->once()->with($export, $account, 'Prospects')->andReturnUsing(function () use ($export) {
            $this->assertSame($export->creator, Auth::user());
            $this->assertSame($export->project, request()->project);
            $this->assertSame(7, getPermissionsTeamId());
            throw new RuntimeException('API unavailable');
        });
        $user = Auth::user();
        $team = getPermissionsTeamId();
        $input = request()->all();
        $job = new PrepareProspectSheet($export, $account, 'Prospects');

        try {
            $job->handle($sheet);
            $this->fail('The API error should propagate to the queue worker.');
        } catch (RuntimeException $exception) {
            $this->assertSame('API unavailable', $exception->getMessage());
        }

        $this->assertSame($user, Auth::user());
        $this->assertSame($team, getPermissionsTeamId());
        $this->assertSame($input, request()->all());
        $job->failed(new RuntimeException('API unavailable: private-token'));
        $status = (new ProspectDownload())->status($export);
        $this->assertSame('failed', $status['status']);
        $this->assertSame('sheet', $status['mode']);
        $this->assertStringNotContainsString('private-token', json_encode($status));
        $this->assertNotEmpty($status['message']);
    }

    protected function service(ProspectExport $rows, array $responses, array &$history): ProspectSheet
    {
        $handler = HandlerStack::create(new MockHandler($responses));
        $handler->push(Middleware::history($history));
        $client = new GoogleDrive();
        $client->setHttpClient(new Client(['handler' => $handler]));
        $sheet = Mockery::mock(ProspectSheet::class, [new ProspectDownload(), $client])
            ->makePartial()->shouldAllowMockingProtectedMethods();
        $sheet->shouldReceive('prospectRows')->andReturn($rows);

        return $sheet;
    }

    protected function jsonResponse(array $body, int $status = 200): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($body));
    }

    protected function export(): Export
    {
        $creator = Mockery::mock(User::class)->makePartial();
        $creator->forceFill(['id' => 1]);
        $creator->shouldReceive('can')->with('prospectExport', Mockery::type(Project::class))->andReturn(true);

        return (new Export())->forceFill([
            'id' => 123, 'project_id' => 7, 'creator_id' => 1, 'count' => 0,
        ])->setRelation('project', (new Project())->forceFill(['id' => 7, 'name' => 'Prospects']))
            ->setRelation('creator', $creator);
    }

    protected function account(): GoogleAccount
    {
        return (new GoogleAccount())->forceFill([
            'id' => 45, 'project_id' => 7, 'for' => 'drive',
            'token' => ['access_token' => 'current-token', 'expires_in' => 3600, 'created' => time()],
        ]);
    }

    protected function rows(Export $export, int $total): ProspectExport
    {
        return new class($export, $total) extends ProspectExport {
            public function __construct(Export $export, protected int $total)
            {
                parent::__construct($export, (int) $export->count);
            }

            protected function getColumns()
            {
                return array_map(fn ($name, $i) => [
                    'type' => self::MAPPING_FIELD_DEFAULT,
                    'field' => (object) ['name' => $name, 'slug' => 'column_' . $i],
                ], ['Identifier', 'Formula', 'Phone', 'Boolean', 'Amount', 'Date', 'Metadata'], range(0, 6));
            }

            protected function getProspects()
            {
                $prospects = [];
                for ($i = $this->skip; $i < min($this->total, $this->skip + $this->limit); ++$i) {
                    $prospects[] = (object) [
                        'column_0' => str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                        'column_1' => '=IMPORTXML("https://example.com","//title")',
                        'column_2' => '+33123456789',
                        'column_3' => true,
                        'column_4' => 1.5,
                        'column_5' => new \DateTimeImmutable('2026-10-29 12:34:56'),
                        'column_6' => ['city' => 'Paris'],
                    ];
                }

                return collect($prospects);
            }
        };
    }
}
