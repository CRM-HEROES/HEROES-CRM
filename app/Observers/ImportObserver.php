<?php

namespace App\Observers;

use App\Jobs\ImportCheckDuplicatedProspects;
use App\Models\Import;
use App\Jobs\ImportGetSummary;
use App\Jobs\ImportHandleDuplicatedProspects;
use App\Jobs\ImportProspects;
use Illuminate\Support\Str;

class ImportObserver
{
    /**
     * Handle the Import "creating" event.
     */
    public function creating(Import $import): void
    {
        $this->generateToken($import);
    }
    
    /**
     * Handle the Import "created" event.
     */
    public function created(Import $import): void
    {
        if (in_array($import->source, ['file', 'google_sheets'], true)) {
            ImportGetSummary::dispatchSync($import);
            $import->refresh();
            $this->autoMapping($import);
        }
    }

    /**
     * Handle the Import "created" event.
     */
    public function updated(Import $import): void
    {
        if (in_array($import->source, ['file', 'google_sheets'], true)) {
            $this->launchOrStop($import);
        }
    }
    
    /**
     * Generate import token
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function generateToken(Import $import)
    {
        $import->token = Str::random(20);
    }
    
    /**
     * Launch or stop import
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function launchOrStop(Import $import)
    {
        if (
            !in_array($import->source, ['file', 'google_sheets'], true) ||
            !$import->isDirty('is_processing') ||
            !$import->is_processing
        ) {
            return;
        }
    
        $import->syncOriginal(['is_processing']);
        $import->processed_at = null;
        $import->save();

        if ($import->duplicates_fields && count($import->duplicates_fields) > 0) {
            $chain = [
                // Check duplicates
                new ImportCheckDuplicatedProspects(
                    $import, 
                    $import->duplicates_fields
                ),
                // Replace duplicates
                new ImportHandleDuplicatedProspects(
                    $import, 
                    "replace_duplicating"
                )
            ];
        } else {
            $chain = [];
        }

        ImportProspects::withChain($chain)->onQueue("imports")->dispatch($import);
    }

    /**
     * Auto mapping
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    protected function autoMapping(Import $import)
    {
        // Import project prospect type fields
        $fields = $import->project
            ->fields()
            ->where('for', 'prospect')
            ->get(['id', 'name', 'slug', 'meta']);
        // Import project prospect type categories
        $categories = $import->project
            ->categories()
            ->where('for', 'prospect')
            ->get(['id', 'name']);
        // Import project previous imports
        $previousImports = $import->project
            ->imports()
            ->where('id', '!=', $import->id)
            ->get(['id', 'headers', 'mapping']);
        // Mapping
        $mapping = $import->mapping ?: array_fill(0, count($import->headers), null);

        foreach ($import->headers as $i => $header) {

            // Find if a field has a slug or name
            // same as the header
            $field = $fields->filter(function($item) use($header) {
                return $item['slug'] == $header || $item['name'] == $header;
            })->first();
            if ($field) {
                $mapping[$i] = ($field->meta ? 'meta->' : '') . $field->slug;
                continue;
            }
            
            // Find if a category has a name
            // same as the header
            $category = $categories->filter(function($item) use($header) {
                return $item['name'] == $header;
            })->first();
            if ($category) {
                $mapping[$i] = 'category->' . $category->id;
                continue;
            }
            
            // Find if there are
            // some already mapped header
            // from previous imports
            // same as the current header
            foreach ($previousImports as $previousImport) {
                if (
                    !is_array($previousImport->headers) || 
                    !is_array($previousImport->mapping) || 
                    count($previousImport->mapping) < count($previousImport->headers)
                ) {
                    continue;
                }

                $index = array_search($header, $previousImport->headers);
                if ($index === FALSE) {
                    continue;
                }

                if (!$previousImport->mapping[$index]) {
                    continue;
                }
                
                $mapping[$i] = $previousImport->mapping[$index];
                break;
            }
        }

        $import->update(['mapping' => $mapping]);
    }
    
}
