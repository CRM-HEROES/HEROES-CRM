<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Field;
use App\Models\Project;
use App\Models\UserSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CategoryToField implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Category $category;
    protected Field $field;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Category $category, Field $field)
    {
        $this->category = $category;
        $this->field = $field;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->checkUsersProspectsTableSetting();
        // Create field having the same name as the category

        $project = $this->category->project;

        $take = 500;
        $skip = 0;

        do {
            $prospects = 
                $project
                    ->prospects()
                    ->whereHas('labels', function($query) {
                        $query->where('category_id', $this->category->id);
                    })
                    ->with(['labels' => function($query) {
                        $query
                            ->where('category_id', $this->category->id)
                            ->select('id', 'name');
                    }])
                    ->skip($skip)
                    ->take($take)
                    ->select('id', 'meta')
                    ->orderBy('created_at', 'desc')
                    ->get();

            $prospects_metas = [];

            foreach ($prospects as $prospect) {
                $labelNames = $prospect->labels->pluck('name')->implode("\n");

                $prospects_metas[] = [
                    "id" => $prospect->id,
                    "meta" =>  array_merge($prospect->meta ? $prospect->meta : [], [$this->field->slug => $labelNames]),
                ];
            }
                
            if ($prospects->count() > 0) {
                DB::table("prospect_label")
                    ->join('labels', 'labels.id', '=', 'prospect_label.label_id')
                    ->where('labels.category_id', $this->category->id)
                    ->whereIn("prospect_label.prospect_id", $prospects->pluck('id')->toArray())
                    ->delete();
                
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

        $this->category->delete();
    }

    /**
     * 
     */
    protected function checkUsersProspectsTableSetting() {
        $settings = UserSetting::where([
            'project_id' => $this->category->project_id,
            'key' => "prospects-table",
            "user_id" => 1
        ])->get();

        foreach ($settings as $setting) {
            $value = $setting->value;

            foreach ($value as $key => $column) {
                if ($column['key'] == 'category->' . $this->category->id)  {
                    $value[$key] = array_merge($value[$key], ['key' => 'meta->' . $this->field->slug]);
                    $setting->update(['value' => $value]);
                    break;
                }
            }
        }
    }
}
