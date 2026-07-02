<?php

namespace App\Http\Controllers\API\Project\Role;

use App\Http\Controllers\Controller;
use App\Models\OrderStep;
use App\Models\Project;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderStepController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Role $role)
    {
        return $role
            ->orderSteps()
            ->select('id', 'name')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated roles.
     */
    public function roles(Project $project, OrderStep $orderStep)
    {
        abort_unless($project->id == $orderStep->project_id, 404);

        return $orderStep
            ->roles()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Role $role, OrderStep $orderStep)
    {
        abort_unless($project->id == $orderStep->project_id, 404);

        $role
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
    public function destroy(Project $project, Role $role, OrderStep $orderStep)
    {
        abort_unless($project->id == $orderStep->project_id, 404);

        $role->orderSteps()->detach($orderStep->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'orderSteps' => 'required',
            'roles' => 'required',
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
            
        // Only roles
        // associated to the current project
        $roles = $project
            ->roles()
            ->whereIn('id', $request->input('roles'))
            ->get(['id'])
            ->map(function($role) {
                return $role->id;
            })
            ->toArray();
            
        switch ($request->input('action')) {
            case "attach":
                $this->bulkActionAttach($roles, $orderSteps);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($roles, $orderSteps);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach order steps
     */
    protected function bulkActionAttach(&$roles, &$orderSteps) {
        // Remove previous data
        $this->bulkActionDetach($roles, $orderSteps);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($orderStep) use($roles, $now) {
            return array_map(function($role) use($orderStep, $now) {
                return [
                    'order_step_id' => $orderStep,
                    'role_id' => $role,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $roles);
        }, $orderSteps);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('role_order_step')->insert($data);
    }

    /**
     * Bulk detach order steps
     */
    protected function bulkActionDetach(&$roles, &$orderSteps) {
        DB::table('role_order_step')
            ->whereIn('order_step_id', $orderSteps)
            ->whereIn('role_id', $roles)
            ->delete();
    }
}
