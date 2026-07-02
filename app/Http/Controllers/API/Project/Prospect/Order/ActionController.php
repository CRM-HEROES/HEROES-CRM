<?php

namespace App\Http\Controllers\API\Project\Prospect\Order;

use App\Http\Controllers\Controller;
use App\Models\OrderAction;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\Order;
use Illuminate\Http\Request;

class ActionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Prospect $prospect, Order $order)
    {
        abort_unless($prospect->id == $order->prospect_id, 404);

        return $order
            ->actions()
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Prospect $prospect, Order $order, OrderAction $action)
    {
        abort_unless($prospect->id == $order->prospect_id, 404);
        abort_unless($project->id == $action->project_id, 404);

        $this->validate($request, [
            'user_id' => 'required'
        ]);

        abort_unless($project->users()->find($request->input('user_id')), 404);

        $order
            ->actions()
            ->withPivot(['user_id', 'creator_id', 'created_at', 'updated_at'])
            ->syncWithoutDetaching([$action->id => array_merge($request->only('user_id', 'paid'), [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ])]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Prospect $prospect, Order $order, OrderAction $action)
    {
        abort_unless($prospect->id == $order->prospect_id, 404);
        abort_unless($project->id == $action->project_id, 404);

        $order->actions()->detach($action->id);

        return ['message' => trans('common.success.detached_resource')];
    }
}
