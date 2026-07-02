<?php

namespace App\Jobs\Import\ProspectItemsHandler;

use App\Jobs\Import\ProspectItemsHandlerInterface;
use App\Models\Import;
use Illuminate\Support\Facades\DB;

class LabelsHandler implements ProspectItemsHandlerInterface
{
    /**
     * 
     */
    public function handle(Import $import, &$prospectsIds, &$items, $date)
    {
        // check that $prospectsIds and $prospectsLabels arrays
        // have the same size
        if (count($prospectsIds) != count($items)) {
            throw new \Exception('Prospects ids count and prospects labels count don\'t match!');
        }

        $data = [];

        foreach ($items as $index => $labels) {
            $prospectId = $prospectsIds[$index];

            // $labels: labels to associate to $prospectId
            foreach ($labels as $labelId) {
                $data[] = [
                    'prospect_id' => $prospectId,
                    'label_id'    => $labelId,
                    'creator_id'  => $import->creator_id,
                    'created_at'  => $date,
                    'updated_at'  => $date,
                ];
            }
        }

        DB::table('prospect_label')->insert($data);
    }
}