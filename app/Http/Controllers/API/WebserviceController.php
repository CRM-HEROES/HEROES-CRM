<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Import;
use App\Models\Label;
use App\Models\Prospect;
use App\Models\User;
use App\Services\Import\GoogleSheetSyncer;
use App\Support\PhoneCountry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebserviceController extends Controller
{
    /**
     *
     * This will be called by an external service
     * to register a prospect on the GMI CRM
     */
    public function prospect(Request $request, Import $import)
    {
        $import->makeVisible('token');

        if ($import->token != $request->input('token', '')) {
            return response('Vous n\'avez pas le droit d\'importer dans cet import.', 400);
        }

        return $this->importCoregistration($request, $import);
    }

    /**
     * Real-time sync trigger for a Google Sheets import: called by the
     * Apps Script trigger installed in the client's own sheet (see the
     * "Copier le script" button in the import's process tab) the moment a
     * cell is edited, instead of waiting for the periodic
     * SyncGoogleSheetImports poll (still runs every 5 min as a fallback,
     * in case the trigger is missing/broken/rate-limited by Google).
     */
    public function syncGoogleSheet(Request $request, Import $import, GoogleSheetSyncer $syncer)
    {
        $import->makeVisible('token');

        Log::info('Google Sheets sync webhook received', [
            'import_id' => $import->id,
            'project_id' => $import->project_id,
            'source' => $import->source,
            'sync_enabled' => (bool) $import->sync_enabled,
            'is_processing' => (bool) $import->is_processing,
            'request_method' => $request->method(),
            'request_ip' => $request->ip(),
            'token_present' => $request->has('token'),
        ]);

        if ($import->token != $request->input('token', '')) {
            Log::warning('Google Sheets sync webhook rejected: invalid token', [
                'import_id' => $import->id,
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => "Vous n'avez pas le droit de synchroniser cet import."], 403);
        }

        if ($import->source !== 'google_sheets' || !$import->sync_enabled) {
            Log::warning('Google Sheets sync webhook rejected: import not eligible', [
                'import_id' => $import->id,
                'source' => $import->source,
                'sync_enabled' => (bool) $import->sync_enabled,
            ]);

            return response()->json(['message' => "La synchronisation automatique n'est pas activée pour cet import."], 400);
        }

        if ($import->is_processing) {
            $staleReset = $syncer->clearStaleProcessingLockIfNeeded($import);

            if ($staleReset) {
                Log::warning('Google Sheets sync webhook recovered a stale processing lock', [
                    'import_id' => $import->id,
                ]);
            } else {
                Log::info('Google Sheets sync webhook skipped because import is already processing', [
                    'import_id' => $import->id,
                ]);

                $syncer->queueRetryIfBusy($import);

                return response()->json([
                    'message' => 'Une synchronisation est déjà en cours. Une nouvelle tentative est déclenchée immédiatement.',
                ], 202);
            }
        }

        // Coalesce a burst of near-simultaneous spreadsheet edits so the
        // trigger fires only once within a short window, instead of
        // re-downloading and reprocessing the same Google Sheet multiple
        // times in a row. The lock is intentionally short-lived to keep the
        // sync near real-time while still absorbing quick edit storms.
        if (!$syncer->claimSyncRequest($import, 15)) {
            $syncer->queueRetryIfBusy($import);

            return response()->json([
                'message' => 'Synchronisation déjà déclenchée récemment. Une nouvelle tentative est déclenchée immédiatement.',
            ], 202);
        }

        $synced = $syncer->sync($import);

        return response()->json(
            ['message' => $synced ? 'Synchronisation lancée.' : "Échec du téléchargement du fichier, nouvelle tentative au prochain passage automatique."],
            $synced ? 200 : 502
        );
    }

    /**
     * 
     * This will be called by an external service 
     * to register a prospect on the GMI CRM
     */
    protected function importCoregistration(Request $request, Import $import)
    {
        // Check that there is a field mapping 
        // between the database 
        // and the information sent from the external server
        if (!$import->headers || !$import->mapping) {
            return response('KO/L\'import via le webservice n\'a pas été encore bien configuré.', 400);
        }

        $prospect = new Prospect([
            'import_id' => $import->id,
            'creator_id' => $import->creator_id,
            'project_id' => $import->project_id
        ]);

        $meta = [];
        $labels = [];

        // update prospect field 
        // according to the import mapping
        foreach ($import->mapping as $index => $attribute) {

            if (is_null($attribute)) continue;

            $param = $import->headers[$index];
            if ($request->has($param)) {
                $value = $request->input($param);

                // update meta field
                if (Str::startsWith($attribute, 'meta->')) {
                    $meta[str_replace('meta->', '', $attribute)] = $value;
                // update classic field
                } else if (Str::startsWith($attribute, 'category->')) {
                    $category = Category::find(str_replace('category->', '', $attribute));

                    if ($category) {
                        if (!empty($value)) {
                            $label = $category->labels()->where('name', $value)->first();

                            // if label is not found inside this category
                            // we create a none validated label
                            if (!$label) {
                                $label = new Label([
                                    'name' => $value,
                                    'color' => "#ffffff",
                                    'bgcolor' => "#000000",
                                    'validated' => 0
                                ]);

                                $category->labels()->save($label);
                            }

                            $labels[] = $label->id;
                        }
                    }
                } else {
                    $prospect[$attribute] = $value;
                }
            }
        }

        $prospect->meta = $meta;
        $prospect->save();

        if ($import->users) {
            // Same "Utilisateurs affectés" indicatif routing as
            // ImportProspects::handleProspectsImportUsers(): only the
            // marked users configured for this prospect's dial code
            // receive it, falling back to every marked user when the
            // prospect has no number or none of them match.
            $markedUsers = User::whereIn('id', $import->users)->get(['id', 'phone_country']);
            $dialCode = PhoneCountry::detectDialCode($prospect->phone_number ?: $prospect->mobile_phone_number);
            $eligibleUsers = PhoneCountry::filterUsersByDialCode($markedUsers, $dialCode);

            $prospect->users()->attach($eligibleUsers->pluck('id'));
        }

        if ($import->groups) {
            $prospect->groups()->attach($import->groups);
        }

        $labels = array_merge($labels, $import->labels ? $import->labels : []);
        $now = \Carbon\Carbon::now();
        $data = array_map(function($label) use($prospect, $now) {
            return [
                'prospect_id' => $prospect->id,
                'label_id'    => $label,
                'deleted_at'  => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }, $labels);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('prospect_label')->insert($data);
        
        /*if ($import->labels) {
            $prospect->labels()->attach($import->labels);
        }

        $prospect->labels()->syncWithoutDetaching($labels);*/
    }
}
