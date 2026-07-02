<?php

namespace App\Http\Controllers\API\Project\Prospect\Order;

use App\Http\Controllers\Controller;
use App\Models\OrderStep;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\Order;

class StepController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect, Order $order)
    {
        abort_unless($prospect->id == $order->prospect_id, 404);

        return $order
            ->steps()
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Prospect $prospect, Order $order, OrderStep $step)
    {
        abort_unless($prospect->id == $order->prospect_id, 404);
        abort_unless($project->id == $step->project_id, 404);

        $order
            ->steps()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$step->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Prospect $prospect, Order $order, OrderStep $step)
    {
        abort_unless($prospect->id == $order->prospect_id, 404);
        abort_unless($project->id == $step->project_id, 404);

        $order->steps()->detach($step->id);

        return ['message' => trans('common.success.detached_resource')];
    }
}
