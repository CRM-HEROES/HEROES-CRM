<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Field;
use App\Models\UserSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class FieldToCategory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Field $field;
    protected Category $category;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Field $field, Category $category)
    {
        $this->field = $field;
        $this->category = $category;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->checkUsersProspectsTableSetting();
        // Create category having the same name as the field

        $project = $this->field->project;

        $this->category->load('labels');

        $attribute = 'meta->' . $this->field->slug;

        $take = 500;
        $skip = 0;
        $date = \Carbon\Carbon::now()->format('Y-m-d h:i:s');

        do {
            $prospects = 
                $project
                    ->prospects()
                    ->select('id', 'meta')
                    ->whereNotNull($attribute)
                    ->skip($skip)
                    ->take($take)
                    ->orderBy('created_at', "desc")
                    ->get();

            $prospects_labels = [];
            $prospects_metas = [];

            foreach ($prospects as $prospect) {
                $meta = $prospect->meta;

                if (!isset($meta[$this->field->slug]) || empty($meta[$this->field->slug])) {
                    continue;
                }

                $labelNames = explode('\n', $meta[$this->field->slug]);

                foreach ($labelNames as $labelName) {
                    if (!($label = $this->category->labels->where('name', $labelName)->first())) {
                        $label = $this->category->labels()->create([
                            'name' => mb_substr($labelName, 0, 191), 
                            'bgcolor' => $this->randColor(rand(250, 350)), 
                            'color' => "#ffffff",
                            'validated' => 0
                        ]);

                        $this->category->load('labels');
                    }

                    $prospects_labels[] = [
                        "prospect_id" => $prospect->id,
                        "label_id" => $label->id,
                        "created_at" => $date,
                        "updated_at" => $date,
                    ];
                }
                
                unset($meta[$this->field->name]);
                $prospects_metas[] = [
                    "id" => $prospect->id,
                    "meta" => $meta,
                ];
            }

            if ($prospects->count() > 0) {
                DB::table("prospect_label")
                    ->join('labels', 'labels.id', '=', 'prospect_label.label_id')
                    ->where('labels.category_id', $this->category->id)
                    ->whereIn("prospect_label.prospect_id", $prospects->pluck('id')->toArray())
                    ->delete();
                
                DB::table("prospect_label")
                    ->insert($prospects_labels);

                $query = "UPDATE `prospects` SET meta = CASE ";
                $query .= implode('', array_map(function($data) {
                    $meta = str_replace("'", "''", json_encode($data['meta']));
                    $meta = str_replace('\\', '\\\\', $meta);
                    $meta = str_replace('"', '\\"', $meta);
                    return "WHEN id = {$data['id']} THEN '{$meta}' ";
                }, $prospects_metas));
                $query .= "END ";
                $query .= "WHERE id IN (";
                $query .=  implode(',', array_map(function($data) {
                    return $data['id'];
                }, $prospects_metas));
                $query .= ")";

                DB::update($query);
            }

            $skip += $take;
        } while ($prospects->count() >= $take);
        
        $this->field->delete();
    }

    /**
     * Generate random color
     * 
     * @param  $sum
     */
    protected function randColor($sum = 300)
    {
        $r = rand(0, min($sum, 255));
        $g = rand(0, min($sum - $r, 255));
        $b = min($sum - $r - $g, 255);

        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }

    /**
     * 
     */
    protected function checkUsersProspectsTableSetting() {
        $settings = UserSetting::where([
            'project_id' => $this->category->project_id,
            'key' => "prospects-table"
        ])
        ->get();

        foreach ($settings as $setting) {
            $value = $setting->value;

            foreach ($value as $key => $column) {
                if ($column['key'] == 'meta->' . $this->field->slug)  {
                    $value[$key] = array_merge($value[$key], ['key' => 'category->' . $this->category->id]);
                    $setting->update(['value' => $value]);
                    break;
                }
            }
        }
    }
}
