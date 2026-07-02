<?php

namespace App\Http\Controllers\API\Project\Order;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Project;
use App\Utils\Product\ImageThumbnail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Product $product)
    {
        abort_unless($project->id == $product->project_id, 404);

        return $product
            ->images()
            ->orderBy('order', 'asc')
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project, Product $product)
    {
        abort_unless($project->id == $product->project_id, 404);

        return $product
            ->images()
            ->create(array_merge(
                $this->storeFile($request, $project), 
                [
                    'creator_id' => auth()->id()
                ]
            ));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Product $product, ProductImage $image)
    {
        abort_unless($project->id == $product->project_id, 404);
        abort_unless($product->id == $image->product_id, 404);

        $disk = Storage::disk('products');
        
        abort_unless($disk->exists($image->path), 404);

        return response($disk->get($image->path))
            ->header('Content-Type', $disk->mimeType($image->path))
            ->header('Content-Disposition', 'attachment; filename="' . $image->name . '"');
    }

    /**
     * Display product image thumbnail
     * Need to install
     * - Imagick
     * - GhostScript
     * To enable to generate thumbail
     */
    public function thumbnail(Project $project, Product $product, ProductImage $image)
    {
        abort_unless($project->id == $product->project_id, 404);
        abort_unless($product->id == $image->product_id, 404);

        $thumbnail = (new ImageThumbnail($image))->generate();

        // Only for page
        // associated to pdf file
        if (!$thumbnail) {
            return response('Cannot generate thumbnail.', 400);
        }

        // Show thumbnail
        return response($thumbnail)->withHeaders([
            'Content-Type' => "image/jpeg",
            'Cache-Control' => 'public, max-age=31536000',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Project $project, Request $request, Product $product, ProductImage $image)
    {
        abort_unless($project->id == $product->project_id, 404);
        abort_unless($product->id == $image->product_id, 404);

        // Store file
        // $fileInfo = $this->storeFile($request, $project);

        $image->update($request->only('order')/*array_merge($fileInfo, $request->only('order'), ['creator_id' => auth()->id()])*/);
        
        return ['message' => trans('common.success.updated_resource')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Product $product, ProductImage $image)
    {
        abort_unless($project->id == $product->project_id, 404);
        abort_unless($product->id == $image->product_id, 404);

        $image->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Store product image
     */
    protected function storeFile(Request $request, Project $project)
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
