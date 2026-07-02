<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\OrderStep;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderStepController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, User $user)
    {
        abort_unless(auth()->user()->can('userOrder-step', $project), 404);

        return $user
            ->orderSteps()
            ->select('id', 'name', 'color', 'bgcolor')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated users.
     */
    public function users(Project $project, OrderStep $orderStep)
    {
        abort_unless(auth()->user()->can('userOrder-step', $project), 404);
        abort_unless($project->id == $orderStep->project_id, 404);

        return $orderStep
            ->users()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, User $user, OrderStep $orderStep)
    {
        abort_unless(auth()->user()->can('userOrder-step', $project), 404);
        abort_unless($project->id == $orderStep->project_id, 404);

        $user
            ->orderSteps()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$orderStep->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, User $user, OrderStep $orderStep)
    {
        abort_unless(auth()->user()->can('userOrder-step', $project), 404);
        abort_unless($project->id == $orderStep->project_id, 404);

        $user->orderSteps()->detach($orderStep->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('userOrder-step', $project), 404);
        
        $this->validate($request, [
            'orderSteps' => 'required',
            'users' => 'required',
            'action' => 'required',
        ]);

        // Only order steps
        // associated to the current project
        $orderSteps = $project
            ->orderSteps()
            ->whereIn('id', $request->input('orderSteps'))
            ->get(['id'])
            ->map(function($orderStep) {
                return $orderStep->id;
            })
            ->toArray();
            
        // Only users
        // associated to the current project
        $users = $project
            ->users()
            ->whereIn('id', $request->input('users'))
            ->get(['id'])
            ->map(function($user) {
                return $user->id;
            })
            ->toArray();
            
        switch ($request->input('action')) {
            case "attach":
                $this->bulkActionAttach($users, $orderSteps);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($users, $orderSteps);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach order steps
     */
    protected function bulkActionAttach(&$users, &$orderSteps) {
        // Remove previous data
        $this->bulkActionDetach($users, $orderSteps);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($orderStep) use($users, $now) {
            return array_map(function($user) use($orderStep, $now) {
                return [
                    'step_id' => $orderStep,
                    'user_id' => $user,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $users);
        }, $orderSteps);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('user_order_step')->insert($data);
    }

    /**
     * Bulk detach order steps
     */
    protected function bulkActionDetach(&$users, &$orderSteps) {
        DB::table('user_order_step')
            ->whereIn('step_id', $orderSteps)
            ->whereIn('user_id', $users)
            ->delete();
    }
}
