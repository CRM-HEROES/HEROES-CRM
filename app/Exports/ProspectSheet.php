<?php

namespace App\Exports;

use App\Jobs\PrepareProspectSheet;
use App\Models\Export;
use App\Models\GoogleAccount;
use App\Services\Google\Drive;
use DateTimeInterface;
use Google\Service\Sheets;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\Spreadsheet;
use RuntimeException;
use Throwable;

class ProspectSheet
{
    public const CONNECTION_MESSAGE = 'Reconnectez le compte Google Drive du projet avant de relancer l’export Google Sheets.';
    public const FAILURE_MESSAGE = 'L’export Google Sheets a échoué. Vérifiez la connexion Google Drive du projet puis réessayez.';

    protected const MAX_BATCH_ROWS = 500;
    protected const MAX_BATCH_BYTES = 1500000;
    protected const REQUEST_OVERHEAD_BYTES = 2048;

    public function __construct(protected ProspectDownload $download, protected Drive $client)
    {
    }

    public function enqueue(Export $export, GoogleAccount $account, string $title): void
    {
        $details = $this->sheetDetails($this->download->status($export));
        $this->download->setStatus($export, 'queued', $details);

        try {
            PrepareProspectSheet::dispatch($export, $account, $title)->onQueue('imports');
        } catch (Throwable $exception) {
            $this->download->setStatus($export, 'failed', $details + ['message' => self::FAILURE_MESSAGE]);
            throw $exception;
        }
    }

    public function prepare(Export $export, GoogleAccount $account, string $title): void
    {
        if ($account->trashed() || $account->for !== 'drive' || $account->project_id != $export->project_id) {
            throw new RuntimeException(self::CONNECTION_MESSAGE);
        }

        $this->authorize($account);
        $service = $this->sheetsService();
        $exporter = $this->prospectRows($export);
        $details = $this->sheetDetails($this->download->status($export));
        $sheetId = $details['sheet_id'] ?? null;
        $batch = [];
        $batchBytes = self::REQUEST_OVERHEAD_BYTES;
        $offset = 0;
        $initialized = false;

        foreach ($exporter->rows() as $row) {
            if (!$initialized) {
                if (count($row) === 0) {
                    throw new RuntimeException('Sélectionnez au moins une colonne à exporter.');
                }

                // Creating a document is not idempotent: never retry this request.
                $this->client->setConfig('retry', ['retries' => 0]);
                $this->client->setConfig('retry_map', []);

                if (!$sheetId) {
                    $spreadsheet = $service->spreadsheets->create(new Spreadsheet([
                        'properties' => ['title' => $title],
                        'sheets' => [[
                            'properties' => [
                                'sheetId' => 0,
                                'title' => 'Prospects',
                                'gridProperties' => [
                                    'columnCount' => count($row),
                                    'rowCount' => 2,
                                    'frozenRowCount' => 1,
                                ],
                            ],
                        ]],
                    ]), ['fields' => 'spreadsheetId']);
                    $sheetId = $spreadsheet->getSpreadsheetId();

                    if (!is_string($sheetId) || !preg_match('/^[a-zA-Z0-9_-]+$/', $sheetId)) {
                        throw new RuntimeException(self::FAILURE_MESSAGE);
                    }
                }

                $details = [
                    'mode' => 'sheet',
                    'sheet_id' => $sheetId,
                    'sheet_url' => 'https://docs.google.com/spreadsheets/d/' . $sheetId . '/edit',
                ];
                // Remember the document before writing so a retry reuses it.
                $this->download->setStatus($export, 'processing', $details);
                $this->client->setConfig('retry', ['retries' => 3, 'initial_delay' => 1, 'max_delay' => 16]);
                $this->client->setConfig('retry_map', [429 => -1, 500 => -1, 503 => -1]);
                $initialized = true;
            }

            $rowData = ['values' => array_map([$this, 'cellData'], array_values($row))];
            // Match the SDK's JSON encoding, including escaped Unicode characters.
            $rowBytes = strlen(json_encode($rowData, JSON_THROW_ON_ERROR)) + 1;
            if ($rowBytes + self::REQUEST_OVERHEAD_BYTES > self::MAX_BATCH_BYTES) {
                throw new RuntimeException('Une ligne est trop volumineuse pour être exportée vers Google Sheets.');
            }

            if ($batch && (count($batch) >= self::MAX_BATCH_ROWS || $batchBytes + $rowBytes > self::MAX_BATCH_BYTES)) {
                $this->writeBatch($service, $sheetId, $batch, $offset);
                $offset += count($batch);
                $batch = [];
                $batchBytes = self::REQUEST_OVERHEAD_BYTES;
                $this->download->setStatus($export, 'processing', $details + ['rows' => max(0, $offset - 1)]);
            }

            $batch[] = $rowData;
            $batchBytes += $rowBytes;
        }

        if ($batch) {
            $this->writeBatch($service, $sheetId, $batch, $offset);
        }

        $this->download->setStatus($export, 'ready', $details + ['rows' => $exporter->getCount()]);
    }

    protected function authorize(GoogleAccount $account): void
    {
        $token = $account->token;
        if (!is_array($token) || empty($token['access_token']) || isset($token['error'])) {
            throw new RuntimeException(self::CONNECTION_MESSAGE);
        }

        $this->client->setAccessToken($token);
        if (!$this->client->isAccessTokenExpired()) {
            return;
        }

        $refreshToken = $token['refresh_token'] ?? null;
        if (!is_string($refreshToken) || $refreshToken === '') {
            throw new RuntimeException(self::CONNECTION_MESSAGE);
        }

        $refreshed = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
        if (!is_array($refreshed) || empty($refreshed['access_token']) || isset($refreshed['error'])) {
            throw new RuntimeException(self::CONNECTION_MESSAGE);
        }

        $token = array_merge($token, $refreshed);
        $token['refresh_token'] = !empty($refreshed['refresh_token']) ? $refreshed['refresh_token'] : $refreshToken;
        $this->client->setAccessToken($token);
        $account->update(['token' => $token]);
    }

    protected function sheetsService(): Sheets
    {
        return new Sheets($this->client);
    }

    protected function prospectRows(Export $export): ProspectExport
    {
        return new ProspectExport($export, (int) $export->count);
    }

    protected function writeBatch(Sheets $service, string $sheetId, array $rows, int $offset): void
    {
        // Both operations are absolute, so repeating a transiently failed batch is safe.
        $service->spreadsheets->batchUpdate($sheetId, new BatchUpdateSpreadsheetRequest([
            'requests' => [
                ['updateSheetProperties' => [
                    'properties' => ['sheetId' => 0, 'gridProperties' => ['rowCount' => max(2, $offset + count($rows))]],
                    'fields' => 'gridProperties.rowCount',
                ]],
                ['updateCells' => [
                    'start' => ['sheetId' => 0, 'rowIndex' => $offset, 'columnIndex' => 0],
                    'rows' => $rows,
                    'fields' => 'userEnteredValue',
                ]],
            ],
        ]));
    }

    protected function cellData($value): array
    {
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        } elseif ($value instanceof \Stringable) {
            $value = (string) $value;
        } elseif (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        }

        if (is_bool($value)) {
            return ['userEnteredValue' => ['boolValue' => $value]];
        }
        if (is_int($value) || is_float($value)) {
            return ['userEnteredValue' => ['numberValue' => $value]];
        }

        // Explicit string values keep leading zeroes and never evaluate formulas.
        return ['userEnteredValue' => ['stringValue' => (string) ($value ?? '')]];
    }

    protected function sheetDetails(array $state): array
    {
        $details = ['mode' => 'sheet'];
        $sheetId = $state['sheet_id'] ?? null;
        if (is_string($sheetId) && preg_match('/^[a-zA-Z0-9_-]+$/', $sheetId)) {
            $details['sheet_id'] = $sheetId;
            $details['sheet_url'] = 'https://docs.google.com/spreadsheets/d/' . $sheetId . '/edit';
        }

        return $details;
    }
}
