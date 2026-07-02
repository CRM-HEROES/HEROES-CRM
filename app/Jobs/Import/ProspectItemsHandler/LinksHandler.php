<?php

namespace App\Jobs\Import\ProspectItemsHandler;

use App\Jobs\Import\ProspectItemsHandlerInterface;
use App\Models\Import;
use Illuminate\Support\Facades\DB;

class LinksHandler implements ProspectItemsHandlerInterface
{
    /**
     * 
     */
    public function handle(Import $import, &$prospectsIds, &$items, $date)
    {
        // check that $prospectsIds and $items arrays
        // have the same size
        if (count($prospectsIds) != count($items)) {
            throw new \Exception('Prospects ids count and prospects links count don\'t match!');
        }

        $data = [];

        foreach ($items as $index => $links) {
            $prospectId = $prospectsIds[$index];

            // $links: link to associate to $prospectId
            foreach ($links as $link) {
                $data[] = array_merge($link, [
                    'prospect_id' => $prospectId,
                    'created_at'  => $date,
                    'updated_at'  => $date,
                ]);
            }
        }

        DB::table('links')->insert($data);
    }
}