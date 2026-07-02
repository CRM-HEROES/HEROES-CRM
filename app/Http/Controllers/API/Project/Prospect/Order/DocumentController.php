<?php

namespace App\Http\Controllers\API\Project\Prospect\Order;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\Order;
use App\Utils\Document\OrderPDFRenderer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function show(Project $project, Prospect $prospect, Order $order, Document $document)
    {
        abort_unless($prospect->id == $order->prospect_id, 404);
        abort_unless($project->id == $document->project_id, 404);
        abort_unless($document->for == 'order', 404);

        $pdfRenderer = (new OrderPDFRenderer($document, $order));
        $outputFile = $pdfRenderer->render();

        if ($document->folder_id) {
            $documentDisk = Storage::disk('documents');
            $folderDisk = Storage::disk('folders');

            // Copy generated document
            $pathinfo = pathinfo($outputFile);
            $path = $project->slug . "/" . $prospect->id . "/" . $document->folder_id . '/' . Str::random(30) . '.pdf';
            $stream = $documentDisk->readStream($project->slug . '/' . $pathinfo['basename']);
            $folderDisk->writeStream($path, $stream);

            $prospect
                ->files()
                ->create([
                    'folder_id' => $document->folder_id,
                    'from_user' => 1,
                    'creator_id' => auth()->id(),
                    'path' => $path,
                    'name' => $document->name,
                    'size' => filesize($outputFile)
                ]);
        }

        return response()
            ->file($outputFile)
            ->deleteFileAfterSend();
    }
}
