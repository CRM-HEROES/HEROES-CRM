<?php

namespace App\Jobs\Import\ImportColumnToField;

use App\Jobs\Import\ImportColumnToFieldInterface;

class LabelField implements ImportColumnToFieldInterface
{
    /**
     * 
     */
    public function handle(&$prospect, $field, $value)
    {
        // Labels names are separated by \n
        // Remove empty labels names
        $labelsNames = array_filter(array_map(function($labelName) {
            return mb_substr(trim($labelName), 0, 100);
        }, explode("\n", $value)));

        // Loop through labels names
        foreach ($labelsNames as $labelName) {
            $label = $this->findOrCreateLabel($field, $labelName);
            $prospect['labels'][] = $label->id;
        }
    }
    
    /**
     * Find if a label having name $labelName
     * exists inside a labels category,
     * if not, create the label
     * 
     * @param  \App\Category  $category parent
     * @param  {string}  $labelName name of the label to find
     */
    protected function findOrCreateLabel($category, $labelName)
    {

        // Find if label already
        // exists in this category
        $label = $category
            ->labels
            ->where('name', $labelName)
            ->first();

        // If not,
        // we create a none validated label
        if (!$label) {
            $label = $this->createNewLabel($category, $labelName);
            // Reload labels list in the category
            $category->load('labels');
        }

        return $label;
    }
    
    /**
     * Create new label
     * inside a labels category
     * 
     * @param  \App\Category  $category parent
     * @param  {string}  $labelName name of the new label
     */
    protected function createNewLabel($category, $labelName)
    {
        return $category
            ->labels()
            ->create([
                'name' => $labelName,
                'color' => "#ffffff",
                'bgcolor' => $this->randColor(rand(250, 350)),
                'validated' => 0
            ]);
    }

    /**
     * Generate random color
     * 
     * @param  $sum
     */
    protected function randColor($sum = 300)
    {
        $r = rand(0, min($sum, 255));
        $g = rand(0, min($sum - $r, 255));
        $b = min($sum - $r - $g, 255);

        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }
}