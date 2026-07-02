<?php

namespace App\Jobs\Import\ProspectItemsHandler;

use App\Jobs\Import\ProspectItemsHandlerInterface;
use App\Models\Import;
use Illuminate\Support\Facades\DB;

class InteractionsHandler implements ProspectItemsHandlerInterface
{
    /**
     * 
     */
    public function handle(Import $import, &$prospectsIds, &$items, $date)
    {
        // check that $prospectsIds and $items arrays
        // have the same size
        if (count($prospectsIds) != count($items)) {
            throw new \Exception('Prospects ids count and prospects interactions count don\'t match!');
        }

        $data = [];

        foreach ($items as $index => $interactions) {
            $prospectId = $prospectsIds[$index];

            // $interactions: interactions to associate to $prospectId
            foreach ($interactions as $interaction) {
                $data[] = array_merge($interaction, [
                    'prospect_id' => $prospectId,
                    'updated_at'  => $date,
                ]);
            }
        }

        DB::table('interactions')->insert($data);
    }
}