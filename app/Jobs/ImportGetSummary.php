<?php

namespace App\Jobs;

use App\Models\Import;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ImportGetSummary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Import $import;
    protected $summaryDataCount = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(Import $import)
    {
        $this->import = $import;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->getFileSummary();
    }
    
    /**
     * Get file summary
     * Header and values
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function getFileSummary()
    {
        // Choose reader type
        // depending on the type of file to import
        $reader = $this->getFileReader();

        // File path
        $filepath = Storage::disk("imports")->path($this->import->path);

        // Open the file using the Memory Spout reader
        $reader->open($filepath);

        // SHEET LOOP
        // Loop through the spreadsheet sheets
        $headers = null;
        $values = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            $isHeaderRow = true;

            $i = 1;
            foreach ($sheet->getRowIterator() as $r) {

                // Get cells values in current row
                $data = [];
                foreach ($r->getCells() as $cell) {
                    $data[] = $cell->getValue();
                }

                if ($isHeaderRow) {
                    $headers = $data;
                    $isHeaderRow = false;
                } else {
                    $values[] = $data;
                }

                ++$i;

                if ($i == $this->summaryDataCount) {
                    break 2;
                }
            }
        }
        
        $reader->close();

        // Update import header and values
        // for summary
        $this->import->headers = $headers;
        $this->import->values = $values;
        $this->import->save();
    }
    
    /**
     * Choose reader type
     * depending on the type of file to import
     */
    protected function getFileReader()
    {

        // Get file extension
        $pathinfoExtension = strtolower(pathinfo($this->import->path, PATHINFO_EXTENSION));

        // CSV
        if ($pathinfoExtension == 'csv') {
            $reader = ReaderEntityFactory::createCSVReader();

            $meta= $this->import->meta;

            if ($this->import->field_delimiter) {
                $reader->setFieldDelimiter($this->import->field_delimiter == 'tab' ? "\t" : $this->import->field_delimiter);
            }
            
            if ($this->import->field_enclosure) {
                $reader->setFieldEnclosure($this->import->field_enclosure);
            }

            return $reader;
        }

        // ODS
        if ($pathinfoExtension == 'ods') {
            return ReaderEntityFactory::createODSReader();
        }

        // XLSX
        return ReaderEntityFactory::createXLSXReader();
    }
}
