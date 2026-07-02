<?php

namespace App\Http\Controllers\API\Project\Order;

use App\Filters\ProductRequestFilters;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, ProductRequestFilters $productRequestFilters)
    {
        return $project
            ->products()
            ->with([
                'images' => function($query) {
                    $query->orderBy('order', 'asc');
                }
            ])
            ->whereNull('archived_at')
            ->filter($productRequestFilters)
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        $metaFieldValues = collect($request->meta)->only(
            $project->fields()
                ->where('for', "product")
                ->where('meta', true)
                ->get()
                ->pluck('slug')
                ->toArray()
        )->toArray();

        $product = $project
            ->products()
            ->create(array_merge($request->only(
                'currency',
                'description',
                'name',
                'price',
                'tax',
                'including_tax'
            ), [
                'meta' => $metaFieldValues,
                'creator_id' => auth()->id(),
                'project_id' => $project->id,
            ]));

        if ($request->hasFile('image')) {
            $product
                ->images()
                ->create(array_merge(
                    $this->storeImage($request, $project), 
                    [
                        'creator_id' => auth()->id()
                    ]
                    ));
        }

        $product->load([
            'images' => function($query) {
                $query->orderBy('order', 'asc');
            }
        ]);

        return $product;
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Product $product)
    {
        abort_unless($project->id == $product->project_id, 404);

        return $product;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Product $product)
    {
        abort_unless($project->id == $product->project_id, 404);

        $metaFieldValues = collect($request->meta)->only(
            $project->fields()
                ->where('for', "product")
                ->where('meta', true)
                ->get()
                ->pluck('slug')
                ->toArray()
        )->toArray();

        $archive = $request->has('archive') ? 
            [
                'archived_at' => $request->input('archive') ? 
                    \Carbon\Carbon::now() : 
                    null
            ] : 
            [];

        $product->update(array_merge(
            $request->only(
                'currency',
                'description',
                'name',
                'price',
                'tax',
                'including_tax',
                'order'
            ), 
            $archive, 
            ['meta' => array_merge($product->meta ?: [], $metaFieldValues)]
        ));

        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Product $product)
    {
        abort_unless($project->id == $product->project_id, 404);

        $product->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
    
    /**
     * Store product image
     */
    protected function storeImage(Request $request, Project $project)
    {
        $file = $request->file('image');
        $originalName = $file->getClientOriginalName();
        $name = Str::random(30) . '.' . pathinfo($originalName)['extension'];

        return [
            'path' => $file->storeAs($project->slug, $name, 'products'),
            'name' => $originalName,
            'size' => $file->getSize()
        ];
    }
}
