<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\Folder;
use App\Models\Prospect;
use App\Utils\Document\ProspectPDFRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProspectDocumentToFolder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Prospect $prospect;
    protected Document $document;
    protected Folder $folder;
    protected $source;

    /**
     * Create a new job instance.
     */
    public function __construct(Prospect $prospect, Document $document, Folder $folder, $source)
    {
        $this->prospect = $prospect;
        $this->document = $document;
        $this->folder = $folder;
        $this->source = $source;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->createFileFromDocument();
    }
    
    /**
     * 
     */
    protected function createFileFromDocument()
    {
        $pdfRenderer = (new ProspectPDFRenderer($this->document, $this->prospect));
        $outputFile = $pdfRenderer->render();
        
        // copy the generated document
        // to attachment disk
        $pathinfo = pathinfo($outputFile);
        $path = $this->prospect->project->slug . "/" . $this->prospect->id . "/" . $this->folder->id . "/" . $pathinfo['basename'];

        $documentDisk = Storage::disk('documents');
        $folderDisk = Storage::disk('folders');
        $folderDisk->writeStream($path, $documentDisk->readStream($pathinfo['basename']));
        $documentDisk->delete($pathinfo['basename']);
        
        return $this->prospect
            ->files()
            ->create([
                'source' => $this->source,
                'folder_id' => $this->folder->id,
                'from_user' => 1,
                'path' => $path,
                'name' => $this->document->name . ".pdf",
                'size' => $folderDisk->size($path),
            ]);
    }
}
