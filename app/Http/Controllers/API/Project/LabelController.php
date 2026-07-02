<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Label;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Category $category)
    {
        abort_unless($project->id == $category->project_id, 404);

        $counts = $this->prospectsCount($project, $category);
        
        return $category
            ->labels()
            ->whereNull('archived_at')
            // ->orderBy('relevance', 'desc')
            ->orderBy('order')
            ->orderBy('name')
            ->get()
            ->transform(function ($label) use($counts) {
                $label->prospects_count = isset($counts[$label->id]) ? $counts[$label->id] : 0;

                return $label;
            });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project, Category $category)
    {
        abort_unless(auth()->user()->can('projectCategoryLabelAdd', $project), 404);

        abort_unless($project->id == $category->project_id, 404);

        $this->validate($request, [
            'name'    => 'required',
            'color'   => 'required',
            'bgcolor' => 'required',
        ]);

        return $category
            ->labels()
            ->create(array_merge($request->only(
                'name',
                'description',
                'color',
                'bgcolor'
            ), [
                'creator_id' => auth()->id(),
            ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Category $category, Label $label)
    {
        abort_unless($project->id == $category->project_id, 404);
        abort_unless($category->id == $label->category_id, 404);

        return $label;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Category $category, Label $label)
    {
        abort_unless(auth()->user()->can('projectCategoryLabelUpdate', $project), 404);
        abort_unless($project->id == $category->project_id, 404);
        abort_unless($category->id == $label->category_id, 404);

        $label->update(
            array_merge(
                $request->only(
                    'name',
                    'description',
                    'color',
                    'bgcolor',
                    'order'
                ), 
                $request->has('archive') ? [
                    'archived_at' => $request->input('archive') ? 
                        \Carbon\Carbon::now() : 
                        null
                ] : []
            )
        );

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Project $project, Category $category, Label $label)
    {
        abort_unless(auth()->user()->can('projectCategoryLabelDelete', $project), 404);
        abort_unless($project->id == $category->project_id, 404);

        abort_unless($category->id == $label->category_id, 404);

        if ($request->has('combine')) {
            if ($with = $category->labels()->find($request->input('combine'))) {
                abort_if($with->id == $label->id, 400);
                $this->combine($category, $label, $with);
            }
        }

        $label->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Combine with another label
     */
    protected function combine(Category $category, Label $label, Label $with)
    {
        if ($category->for == 'prospect') {
            $table = "prospect_label";
        } else if ($category->for == 'order') {
            $table = "order_label";
        } else if ($category->for == 'user') {
            $table = "user_label";
        } else {
            return response('Unknown category for.', 400);
        }

        // Avoid duplicates
        DB::table("$table as A")
            ->join("$table as B", "A.prospect_id", "=", "B.prospect_id")
            ->where('A.label_id', $with->id)
            ->where('B.label_id', $label->id)
            ->delete();

        // Replace $label with $with
        // from $table
        DB::table($table)
            ->where('label_id', $label->id)
            ->update([
                'label_id' => $with->id
            ]);
    }

    /**
     * 
     */
    public function orders(Request $request, Project $project, Category $category) 
    {
        abort_unless(auth()->user()->can('projectCategoryLabelUpdate', $project), 404);
        abort_unless($project->id == $category->project_id, 404);

        $this->validate($request, [
            'orders' => 'required',
        ]);

        $labels = $category->labels;

        foreach ($request->input('orders') as $order) {
            $label = $labels->where('id', $order['label'])->first();

            if ($label) {
                $label->update([
                    'order' => $order['order']
                ]);
            }
        }

        return ['message' => trans('common.success.updated_resource')];
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function bulk(Request $request, Project $project, Category $category)
    {
        abort_unless($project->id == $category->project_id, 404);

        $this->validate($request, [
            'labels' => 'required',
            'action' => 'required',
        ]);

        // Only labels
        // associated to the current project
        $labels = $category
            ->labels()
            ->whereIn('id', $request->input('labels'));
            // ->withTrashed();

        switch ($request->input('action')) {
            case "delete":
                abort_unless(auth()->user()->can('projectCategoryLabelDelete', $project), 404);
                $labels->delete();
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }
    
    protected function prospectsCount(Project $project, Category $category) {
        return cache()->remember(
            "user." . auth()->id() . ".category." . $category->id . ".prospects.count", 
            120, /* 2mn */
            function() use($project, $category) {
                $counts = DB::table('prospect_label')
                    ->join('labels', 'labels.id', '=', 'prospect_label.label_id')
                    ->join('prospects', 'prospects.id', '=', 'prospect_label.prospect_id')
                    ->select('label_id', DB::raw('count(*) as prospects_count'))
                    ->groupBy('label_id')
                    ->when(!auth()->user()->can('', $project), function($query) use($project) {
                        $query
                            ->leftJoin('prospect_user', 'prospect_label.prospect_id', '=', 'prospect_user.prospect_id')
                            ->leftJoin('prospect_group', 'prospect_group.prospect_id', '=', 'prospect_user.prospect_id')
                            ->where(function($query) use($project) {
                                $groups = DB::table('user_group')
                                    ->join('groups', 'groups.project_id', '=', DB::raw($project->id))
                                    ->get(['groups.id'])
                                    ->pluck('id');

                                $query
                                    ->where('prospect_user.user_id', auth()->id())
                                    ->orWhereIn('prospect_group.group_id', $groups);
                            });
                    })
                    ->where('labels.category_id', DB::raw($category->id))
                    ->whereNull('prospects.deleted_at')
                    ->get();
                
                $result = [];

                foreach ($counts as $count) {
                    $result[$count->label_id] = $count->prospects_count;
                }

                return $result;
            });
    }
}
