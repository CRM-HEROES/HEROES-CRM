<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Import;
use App\Models\Label;
use App\Models\Prospect;
use Illuminate\Http\Request;
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
