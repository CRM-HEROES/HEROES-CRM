<?php

namespace App\Http\Controllers\API\Project\Role;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Product;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Role $role)
    {
        return $role
            ->products()
            ->select('id', 'name')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated roles.
     */
    public function roles(Project $project, Product $product)
    {
        abort_unless($project->id == $product->project_id, 404);

        return $product
            ->roles()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Role $role, Product $product)
    {
        abort_unless($project->id == $product->project_id, 404);

        $role
            ->products()
            ->withPivot('creator_id', 'created_at', 'updated_at')
            ->syncWithoutDetaching([$product->id => [
                'creator_id' => auth()->id(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]]);

        return ['message' => trans('common.success.attached_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Role $role, Product $product)
    {
        abort_unless($project->id == $product->project_id, 404);

        $role->products()->detach($product->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        $this->validate($request, [
            'products' => 'required',
            'roles' => 'required',
            'action' => 'required',
        ]);

        // Only products
        // associated to the current project
        $products = $project
            ->products()
            ->whereIn('id', $request->input('products'))
            ->get(['id'])
            ->map(function($product) {
                return $product->id;
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
                $this->bulkActionAttach($roles, $products);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($roles, $products);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach products
     */
    protected function bulkActionAttach(&$roles, &$products) {
        // Remove previous data
        $this->bulkActionDetach($roles, $products);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($product) use($roles, $now) {
            return array_map(function($role) use($product, $now) {
                return [
                    'product_id' => $product,
                    'role_id' => $role,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $roles);
        }, $products);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('role_product')->insert($data);
    }

    /**
     * Bulk detach products
     */
    protected function bulkActionDetach(&$roles, &$products) {
        DB::table('role_product')
            ->whereIn('product_id', $products)
            ->whereIn('role_id', $roles)
            ->delete();
    }
}
