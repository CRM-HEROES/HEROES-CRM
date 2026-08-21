<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Downloads the XLSX export of a public Google Sheets URL (all of its
 * sheets/tabs) and stores it on the "imports" disk, exactly like an
 * uploaded file. Shared between the manual "import from URL" flow
 * (ImportController) and the periodic auto-sync (SyncGoogleSheetImports).
 */
class GoogleSheetDownloader
{
    /**
     * Extract the spreadsheet id from a Google Sheets share URL.
     */
    public function extractSpreadsheetId(string $url): string
    {
        if (!preg_match('~docs\.google\.com/spreadsheets/d/([a-zA-Z0-9_-]+)~', $url, $matches)) {
            throw ValidationException::withMessages([
                'url' => "Ce lien Google Sheets n'est pas valide.",
            ]);
        }

        return $matches[1];
    }

    /**
     * Export the whole workbook as XLSX (not a single-tab CSV export) so
     * that every sheet/tab in the document gets imported, not just the
     * one the shared URL happens to point to. ImportProspects reads every
     * sheet of the workbook via Box Spout's sheet iterator.
     */
    public function download(string $spreadsheetId, string $projectSlug): array
    {
        $exportUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=xlsx";

        try {
            $response = Http::timeout(15)->connectTimeout(5)->get($exportUrl);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw ValidationException::withMessages([
                'url' => "Impossible de contacter Google Sheets (problème réseau). Réessayez.",
            ]);
        }

        if ($response->failed() || Str::contains($response->header('Content-Type', ''), 'text/html')) {
            throw ValidationException::withMessages([
                'url' => "Impossible de récupérer ce fichier Google Sheets. Vérifiez que le lien de partage autorise \"Tous les utilisateurs disposant du lien\" à voir le document.",
            ]);
        }

        $name = Str::random(30) . '.xlsx';
        $path = $projectSlug . '/' . $name;

        Storage::disk('imports')->put($path, $response->body());

        return [
            'path' => $path,
            'size' => strlen($response->body()),
        ];
    }
}
