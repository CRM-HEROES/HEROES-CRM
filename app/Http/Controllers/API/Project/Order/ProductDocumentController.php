<?php

namespace App\Http\Controllers\API\Project\Order;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductDocument;
use App\Models\Project;
use App\Utils\Product\DocumentThumbnail;
use Google\Service\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, Product $product)
    {
        abort_unless($project->id == $product->project_id, 404);

        return $product
            ->documents()
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project, Product $product)
    {
        abort_unless($project->id == $product->project_id, 404);

        return $product
            ->documents()
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
    public function show(Project $project, Product $product, ProductDocument $document)
    {
        abort_unless($project->id == $product->project_id, 404);
        abort_unless($product->id == $document->product_id, 404);

        $disk = Storage::disk('products');
        
        abort_unless($disk->exists($document->path), 404);

        return response($disk->get($document->path))
            ->header('Content-Type', $disk->mimeType($document->path))
            ->header('Content-Disposition', 'attachment; filename="' . $document->name . '"');
    }

    /**
     * Display product document thumbnail
     * Need to install
     * - Imagick
     * - GhostScript
     * To enable to generate thumbail
     */
    public function thumbnail(Project $project, Product $product, ProductDocument $document)
    {
        abort_unless($project->id == $product->project_id, 404);
        abort_unless($product->id == $document->product_id, 404);

        $thumbnail = (new DocumentThumbnail($document))->generate();

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
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Product $product, ProductDocument $document)
    {
        abort_unless($project->id == $product->project_id, 404);
        abort_unless($product->id == $document->product_id, 404);

        $document->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Store file file
     */
    protected function storeFile(Request $request, Project $project)
    {
        $file = $request->file('document');
        $originalName = $file->getClientOriginalName();
        $name = Str::random(30) . '.' . pathinfo($originalName)['extension'];

        return [
            'path' => $file->storeAs($project->slug, $name, 'products'),
            'name' => $originalName,
            'size' => $file->getSize()
        ];
    }
}
