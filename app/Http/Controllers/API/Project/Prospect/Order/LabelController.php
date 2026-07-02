<?php

namespace App\Http\Controllers\API\Project\Prospect\Order;

use App\Http\Controllers\Controller;
use App\Models\Label;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\Order;

class LabelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect, Order $order)
    {
        abort_unless($prospect->id == $order->prospect_id, 404);

        return $order
            ->labels()
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Prospect $prospect, Order $order, Label $label)
    {
        abort_unless($prospect->id == $order->prospect_id, 404);
        abort_unless($project->id == $label->project_id, 404);
        abort_unless($label->category->for == "order", 404);

        $order->labels()->withPivot('creator_id', 'created_at', 'updated_at')->syncWithoutDetaching([$label->id => [
            'creator_id' => auth()->id(),
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Prospect $prospect, Order $order, Label $label)
    {
        abort_unless($prospect->id == $order->prospect_id, 404);
        abort_unless($project->id == $label->project_id, 404);
        abort_unless($label->category->for == "order", 404);

        $order->labels()->detach($label->id);

        return ['message' => trans('common.success.detached_resource')];
    }
}
