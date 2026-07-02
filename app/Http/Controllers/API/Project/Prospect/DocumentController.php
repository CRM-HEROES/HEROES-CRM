<?php

namespace App\Http\Controllers\API\Project\Prospect;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Project;
use App\Models\Prospect;
use App\Utils\Document\ProspectPDFRenderer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function show(Project $project, Prospect $prospect, Document $document)
    {
        // Document associated to the current project
        abort_unless($project->id == $document->project_id, 404, 'Unknown document template');
        // Document associated to "prospect"
        abort_unless($document->for == 'prospect', 404, 'Document template not for prospect');

        // Render prospect document
        $pdfRenderer = (new ProspectPDFRenderer($document, $prospect));
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

        return response()->file($outputFile)->deleteFileAfterSend();
    }
}
