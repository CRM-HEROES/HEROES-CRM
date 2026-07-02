<?php

namespace App\Utils\Document;

use App\Models\Document;
use App\Models\DocumentField;
use App\Models\DocumentPage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDF;
use setasign\Fpdi\Fpdi;

class PDFRenderer
{
    protected Document $document;
    protected $renderers = [];
    protected $appendedFiles = [];
    protected $pages = [];
    protected $addSignature = true;
    
    public function __construct(Document $document, $pages = [])
    {
        $this->document = $document;
        $this->pages = $pages;
    }

    /**
     * Render
     */
    public function render()
    {
        $this->setupFontsCacheDir();

        $document = $this->document;
        $fpdi = new FPDI;

        $disk = Storage::disk('documents');

        foreach (
            $this->document
                ->pages()
                ->orderBy('page')
                ->when(!empty($this->pages), function($query) {
                    $query->whereIn('page', $this->pages);
                })
                ->get() as $page
        ) {

            // Add new page and
            // Define page size and orientation
            
            $fpdi->AddPage($page->orientation, array(intval($page->width), intval($page->height)));
    
            /////////////////////////////////////////////////
            
            // Define page template (background)
            if ($page->file) {
                $this->printPage($fpdi, $disk->path($page->file->path), $page->file_page);
            }

            /////////////////////////////////////////////////
            
            // Define page foreground

            $fields = $this->getDocumentPageFields($document, $page);

            if (count($fields) > 0) {
                $tmpfile = $this->createFieldsTemplatePage($document, $page, $fields);

                // Define page template (foreground)
                $this->printPage($fpdi, $tmpfile, 1);

                // Remove temporary file
                Storage::delete($tmpfile);
            }
        }

        // Add appended files
        // at the end of the generated documents
        $this->printAppendedFiles($fpdi);

        // Generated PDF document
        $outputFile = Storage::disk('documents')->path($document->project->slug . "/" . Str::random(30) . ".pdf");
        $fpdi->Output($outputFile, 'F');

        return $outputFile; // ->deleteFileAfterSend(true);
    }

    /**
     * Add renderer
     */
    public function addRenderer(FieldRenderer $renderer)
    {
        $this->renderers[] = $renderer;
        
        return $this;
    }

    /**
     * Append file at the and of the generated document
     */
    public function appendFile($file)
    {
        $this->appendedFiles[] = $file;

        return $this;
    }

    /**
     * Append file at the and of the generated document
     */
    public function setAddSignature($addSignature)
    {
        $this->addSignature = $addSignature;

        return $this;
    }

    /**
     * Compute field content
     * 
     * @param $fields
     * @param $document
     */
    protected function computeFieldsContent($fields, DocumentPage $page)
    {
        return $fields->transform(function($field) use($page) {
            foreach ($this->renderers as $renderer) {
                $field->content = $renderer->render($field, $page);
            }

            return $field;
        });
    }

    /**
     * Get fields in current document page
     * 
     * @param $document
     * @param $page
     */
    protected function getDocumentPageFields(Document $document, DocumentPage $page)
    {
        $fields = $document
            ->fields()
            ->where(function($query) use($page) {
                $query
                    ->where('page_id', $page->id)
                    ->orWhereNull('page_id');
            })
            ->get();

        // Sign document page
        if ($this->addSignature) {
            $fields->push($this->copyright());
        }

        return $this->computeFieldsContent($fields, $page);
    }

    /**
     * Print page in current document page
     */
    protected function printPage($fpdi, $file, $page = 1)
    {
        $fpdi->setSourceFile($file);
        $template = $fpdi->importPage($page);
        $fpdi->useTemplate($template);
    }

    /**
     * Print page in current document page
     */
    protected function createFieldsTemplatePage($document, $page, $fields)
    {
        $disk = Storage::disk('documents');

        $pdf = PDF::loadView(
            'document.index', 
            compact('document', 'page', 'fields')
        );

        // Store temporary foreground pdf
        $content = $pdf->download()->getOriginalContent();
        $tmpName = Str::random(30) . '.pdf';
        $disk->put($tmpName, $content);

        return $disk->path($tmpName);
    }

    /**
     * Print appended files at the and of the generated document
     */
    protected function printAppendedFiles($fpdi)
    {
        foreach ($this->appendedFiles as $file) {
            $this->printAppendedFile($fpdi, $file);
        }
    }

    /**
     * Print appended file at the and of the generated document
     */
    protected function printAppendedFile($fpdi, $file)
    {
        $count = $fpdi->setSourceFile($file);

        for ($i = 1; $i <= $count; $i++) {
            $template = $fpdi->importPage($i);
            $size     = $fpdi->getTemplateSize($template);
            $fpdi->AddPage($size['orientation'], $size['width'], $size['height']);
            $this->printPage($fpdi, $file, $i);
        }
    }

    /**
     * 
     */
    protected function setupFontsCacheDir()
    {
        $disk = Storage::disk('fonts');

        $folder = $this->document->project->slug . "/";
        if (!$disk->exists($folder)) {
            return $disk->makeDirectory($folder);
        }

        $folder = $folder . "cache/";
        if (!$disk->exists($folder)) {
            return $disk->makeDirectory($folder);
        }
        
        $folder = $folder . $this->document->id . "/";
        if (!$disk->exists($folder)) {
            return $disk->makeDirectory($folder);
        }

        Config::set([
            'dompdf.options.font_dir' => $disk->path($folder),
            'dompdf.options.font_cache' => $disk->path($folder),
        ]);
    }

    /**
     * Heroes CRM 
     */
    protected function copyright()
    {
        return new DocumentField([
            'type' => "html",
            'content' => "&copy; " . env("APP_NAME"),
            'style' => [
                'bottom' => '120px',
                'transform-origin' => 'top right',
                'right' => '-80px',
                'transform' => 'rotate(90deg)',
                "color" => "#AAAAAA",
                "font-size" => "15px",
                "white-space" => "nowrap",
            ]
        ]);
    }
}