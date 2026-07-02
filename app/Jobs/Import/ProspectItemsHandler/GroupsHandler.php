<?php

namespace App\Jobs\Import\ProspectItemsHandler;

use App\Jobs\Import\ProspectItemsHandlerInterface;
use App\Models\Import;
use Illuminate\Support\Facades\DB;

class GroupsHandler implements ProspectItemsHandlerInterface
{
    /**
     * 
     */
    public function handle(Import $import, &$prospectsIds, &$items, $date)
    {
        // check that $prospectsIds and $prospectsUsers arrays
        // have the same size
        if (count($prospectsIds) != count($items)) {
            throw new \Exception('Prospects ids count and prospects groups count don\'t match!');
        }

        $data = [];

        foreach ($items as $index => $groups) {
            $prospectId = $prospectsIds[$index];

            // $groups: groups to associate to $prospectId
            foreach ($groups as $group) {
                $data[] = [
                    'prospect_id' => $prospectId,
                    'group_id'     => $group,
                    'created_at'  => $date,
                    'updated_at'  => $date,
                ];
            }
        }

        DB::table('prospect_group')->insert($data);
    }
}