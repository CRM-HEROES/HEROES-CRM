<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Import;
use App\Models\Label;
use App\Models\Prospect;
use App\Services\Import\GoogleSheetSyncer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

        if ($import->token != $request->input('token', '')) {
            return response()->json(['message' => "Vous n'avez pas le droit de synchroniser cet import."], 403);
        }

        if ($import->source !== 'google_sheets' || !$import->sync_enabled) {
            return response()->json(['message' => "La synchronisation automatique n'est pas activée pour cet import."], 400);
        }

        if ($import->is_processing) {
            return response()->json(['message' => 'Une synchronisation est déjà en cours.'], 202);
        }

        // Debounce: a paste of many cells, or a burst of quick edits, fires
        // the Apps Script trigger once per edit — without this, that would
        // re-download and re-process the whole sheet on every single one.
        // Explicitly on the redis store (same fallback pattern as
        // ImportProspects's processing lock): the default cache store is
        // "array" in local/dev, which doesn't persist across requests and
        // would make this cooldown a silent no-op there.
        $cooldownKey = 'google-sheet-sync-webhook-cooldown-' . $import->id;

        try {
            $cache = Cache::store('redis');
            // Cache store connections are lazy: Cache::store('redis') alone
            // never throws, the actual connection attempt (and failure)
            // only happens on first real operation below.
            $cache->has('google-sheet-sync-webhook-cooldown-probe');
        } catch (\Throwable $e) {
            $cache = Cache::store();
        }

        if ($cache->has($cooldownKey)) {
            return response()->json(['message' => 'Synchronisation déjà déclenchée récemment, réessayez dans quelques secondes.'], 202);
        }

        $cache->put($cooldownKey, true, 10);

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
            $prospect->users()->attach($import->users);
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
