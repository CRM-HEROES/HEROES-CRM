<?php

namespace App\Http\Controllers\API\Project\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, User $user)
    {
        abort_unless(auth()->user()->can('userProduct', $project), 404);
        
        return $user
            ->products()
            ->select('id', 'name', 'color', 'bgcolor')
            ->where('project_id', $project->id)
            ->get();
    }

    /**
     * Display associated users.
     */
    public function users(Project $project, Product $product)
    {
        abort_unless(auth()->user()->can('userProduct', $project), 404);
        abort_unless($project->id == $product->project_id, 404);

        return $product
            ->users()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, User $user, Product $product)
    {
        abort_unless(auth()->user()->can('userProduct', $project), 404);
        abort_unless($project->id == $product->project_id, 404);

        $user
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
    public function destroy(Project $project, User $user, Product $product)
    {
        abort_unless(auth()->user()->can('userProduct', $project), 404);
        abort_unless($project->id == $product->project_id, 404);

        $user->products()->detach($product->id);

        return ['message' => trans('common.success.detached_resource')];
    }

    /**
     * Update multiple resources in storage.
     */
    public function bulk(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('userProduct', $project), 404);

        $this->validate($request, [
            'products' => 'required',
            'users' => 'required',
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
                $this->bulkActionAttach($users, $products);
                return ['message' => trans('common.success.updated_resource')];            

            case "detach":
                $this->bulkActionDetach($users, $products);
                return ['message' => trans('common.success.deleted_resource')];

            default:
                return response('Unknown bulk action.', 400);
        }
    }

    /**
     * Bulk attach products
     */
    protected function bulkActionAttach(&$users, &$products) {
        // Remove previous data
        $this->bulkActionDetach($users, $products);

        // Update datas
        $now = \Carbon\Carbon::now();
        $data = array_map(function($product) use($users, $now) {
            return array_map(function($user) use($product, $now) {
                return [
                    'product_id' => $product,
                    'user_id' => $user,
                    'creator_id' => auth()->id(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $users);
        }, $products);
        $data = array_reduce($data, function($carry, $data) {
            return array_merge($carry, $data);
        }, []);

        DB::table('user_product')->insert($data);
    }

    /**
     * Bulk detach products
     */
    protected function bulkActionDetach(&$users, &$products) {
        DB::table('user_product')
            ->whereIn('product_id', $products)
            ->whereIn('user_id', $users)
            ->delete();
    }
}
