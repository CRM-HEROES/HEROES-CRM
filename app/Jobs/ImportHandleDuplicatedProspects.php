<?php

namespace App\Jobs;

use App\Models\Import;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ImportHandleDuplicatedProspects implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const MAPPING_FIELD_CLASSIC = 0;
    const MAPPING_FIELD_META = 1;
    const MAPPING_FIELD_CATEGORY = 2;

    protected Import $import;
    protected $action;
    protected $prospects;
    protected $categories;
    protected $mapping;

    /**
     * Create a new job instance.
     */
    public function __construct(Import $import, $action, $prospects = [])
    {
        $this->import = $import;
        $this->action = $action;
        $this->prospects = $prospects;
        $this->categories = $this->getCategories();
        $this->mapping = $this->getImportMappingFields();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $duplicates = $this->getDuplicates();

        switch ($this->action) {
            case "remove_duplicating":
                $this->removeDuplicating($duplicates);
                break;
            case "remove_duplicates":
                $this->removeDuplicates($duplicates);
                break;
            case "replace_duplicating":
                $this->replaceDuplicating($duplicates);
                break;
        }
    }
    
    /**
     * Get list of labels categories
     * with the associated labels
     */
    protected function getCategories()
    {
        return $this->import->project
            ->categories()
            ->select('id')
            ->with('labels')
            ->get();
    }

    /**
     * Get import mapping fields
     */
    protected function getImportMappingFields()
    {
        return array_map(function($attribute) {

            // 1. Not mapped column
            if (is_null($attribute)) {
                return null;
            }

            // 2. Meta field
            if (Str::startsWith($attribute, 'meta->')) {
                return [
                    'type'  => ImportHandleDuplicatedProspects::MAPPING_FIELD_META,
                    'field' => str_replace('meta->', '', $attribute)
                ];
            }

            // 3. Category labels
            if (Str::startsWith($attribute, 'category->')) {
                $categoryId = str_replace('category->', '', $attribute);

                // Check if category exists
                $category = $this->categories->find($categoryId);
                if (!$category) {
                    // else, do not map the column
                    return null;
                }

                return [
                    'type'  => ImportHandleDuplicatedProspects::MAPPING_FIELD_CATEGORY,
                    'field' => $category
                ];
            }

            // 4. Default field
            return [
                'type'  => ImportHandleDuplicatedProspects::MAPPING_FIELD_CLASSIC,
                'field' => $attribute
            ];
        }, $this->import->mapping);
    }
    
    /**
     * Get list of duplicated prospects
     */
    protected function getDuplicates()
    {
        return $this->import->project
            ->prospects()
            ->with('duplicate')
            ->whereNotNull('duplicate_id')
            ->when(!empty($this->prospects), function($query) {
                $query->whereIn('prospects.id', $this->prospects);
            })
            ->get();
    }

    /**
     * Handle duplicate
     */
    protected function handleDuplicate($prospect)
    {
        $duplicate = $prospect->duplicate;

        foreach ($this->mapping as $mapping) {
            if (!$mapping) {
                continue;
            }
            
            $field = $mapping['field'];
            
            switch ($mapping['type']) {

                // Meta
                case ImportHandleDuplicatedProspects::MAPPING_FIELD_META:
                    $meta = $prospect->meta ?: [];
                    if (
                        $duplicate->meta && 
                        isset($duplicate->meta[$field]) && 
                        $duplicate->meta[$field]
                    ) {
                        $meta[$field] = $duplicate->meta[$field];
                    }
                    $prospect->meta = $meta;
                    break;
                
                // Category
                case ImportHandleDuplicatedProspects::MAPPING_FIELD_CATEGORY:
                    $prospect
                        ->labels()
                        ->detach(
                            $prospect
                                ->labels()
                                ->where('category_id', $field->id)
                                ->get()
                                ->pluck('id')
                        );
                    $prospect
                        ->labels()
                        ->syncWithoutDetaching(
                            $duplicate
                                ->labels()
                                ->where('category_id', $field->id)
                                ->get()
                                ->pluck('id')
                        );
                    break;
                
                // Default
                default:
                    $prospect->{$field} = $duplicate->{$field};
                    break;
            }
        }

        if ($this->import->labels) {
            $prospect
                ->labels()
                ->syncWithoutDetaching(
                    $this->import->labels
                );    
        }
        
        if ($this->import->users) {
            $prospect
                ->users()
                ->syncWithoutDetaching(
                    $this->import->users
                );    
        }
        
        if ($this->import->groups) {
            $prospect
                ->users()
                ->syncWithoutDetaching(
                    $this->import->groups
                );    
        }
        
        $prospect->save();
    }

    /**
     * Replace duplicates
     */
    protected function replaceDuplicating($duplicates)
    {
        foreach ($duplicates as $prospect) {
            $this->handleDuplicate($prospect);
        }
        
        $this->removeDuplicates($duplicates);
    }

    /**
     * Remove duplicates
     */
    protected function removeDuplicates($duplicates)
    {
        $ids = [];

        foreach ($duplicates as $prospect) {
            $ids[] = $prospect->duplicate->id;
        }

        $ids = array_unique($ids);
        $this->import->project->prospects()->whereIn('id', $ids)->delete();
    }
    
    /**
     * Remove duplicating
     */
    protected function removeDuplicating($duplicates)
    {
        $this->import->project->prospects()->whereIn('id', $duplicates->pluck('id'))->delete();
    }
}
