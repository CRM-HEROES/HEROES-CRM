<?php

namespace App\Http\Controllers\API\Project;

use App\Filters\CommissionRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\OrderAction;
use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, CommissionRequestFilters $filters)
    {
        $commissions = Commission
            ::whereHas('product', function($query) use($project) {
                $query->where('project_id', $project->id);
            })
            ->whereHas('action', function($query) use($project) {
                $query->where('project_id', $project->id);
            })
            ->filter($filters)
            ->get();

        return $commissions;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, User $user, Product $product, OrderAction $action)
    {
        abort_unless(auth()->user()->can('projectUserCommissionAdd', $project), 404);
        abort_unless($project->users()->find($user->id), 404);
        abort_unless($project->products()->find($product->id), 404);
        abort_unless($project->orderActions()->find($action->id), 404);

        Commission
            ::updateOrInsert([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'action_id' => $action->id,
            ], array_merge($request->only('value', 'type', 'including_tax'), [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, User $user, Product $product, OrderAction $action)
    {
        abort_unless(auth()->user()->can('projectUserCommissionDelete', $project), 404);
        abort_unless($project->users()->find($user->id), 404);
        abort_unless($project->products()->find($product->id), 404);
        abort_unless($project->orderActions()->find($action->id), 404);

        Commission
            ::where([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'action_id' => $action->id,
            ])
            ->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
    
    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'actions' => 'required',
            'users' => 'required',
            'products' => 'required',
            'action' => 'required',
        ]);

        // Only actions
        // associated to the current project
        $actions = $project
            ->orderActions()
            ->whereIn('id', $request->input('actions'))
            ->get(['id'])
            ->map(function($action) {
                return $action->id;
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
            
        // Only products
        // associated to the current project
        $products = $project
            ->orderActions()
            ->whereIn('id', $request->input('products'))
            ->get(['id'])
            ->map(function($product) {
                return $product->id;
            })
            ->toArray();

        switch ($request->input('action')) {
            case "attach":
                abort_unless(auth()->user()->can('projectUserCommissionAdd', $project), 404);
                $this->bulkActionAttach($actions, $users, $products, $request->input('value'), $request->input('type'), $request->input('including_tax'));
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                abort_unless(auth()->user()->can('projectUserCommissionDelete', $project), 404);
                $this->bulkActionDetach($actions, $users, $products);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }
    
    /**
     * Bulk attach threads
     */
    protected function bulkActionAttach(&$actions, &$users, &$products, $value, $type, $including_tax)
    {
        // Remove previous data
        $this->bulkActionDetach($actions, $users, $products);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($action) use($users, $products, $value, $type, $including_tax, $now) {
            return array_map(function($user) use($action, $products, $value, $type, $including_tax, $now) {
                return array_map(function($product) use($action, $user, $value, $type, $including_tax, $now) {
                    return [
                        'action_id' => $action,
                        'user_id' => $user,
                        'product_id' => $product,
                        'value' => $value,
                        'type' => $type,
                        'including_tax' => $including_tax,
                        'creator_id' => auth()->id(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }, $products);
            }, $users);
        }, $actions);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, array_reduce($data, function($carry2, $data2) {
                return array_merge($carry2, $data2);
            }, []));
        }, []);

        DB::table('commissions')->insert($data);
    }

    /**
     * Bulk detach threads
     */
    protected function bulkActionDetach(&$actions, &$users, &$products)
    {
        DB::table('commissions')
            ->whereIn('action_id', $actions)
            ->whereIn('user_id', $users)
            ->whereIn('product_id', $products)
            ->delete();
    }
}
