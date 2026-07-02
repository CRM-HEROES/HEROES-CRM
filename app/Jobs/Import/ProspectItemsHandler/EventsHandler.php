<?php

namespace App\Jobs\Import\ProspectItemsHandler;

use App\Jobs\Import\ProspectItemsHandlerInterface;
use App\Models\Import;
use Illuminate\Support\Facades\DB;

class EventsHandler implements ProspectItemsHandlerInterface
{
    /**
     * 
     */
    public function handle(Import $import, &$prospectsIds, &$items, $date)
    {
        // check that $prospectsIds and $prospectsEvents arrays
        // have the same size
        if (count($prospectsIds) != count($items)) {
            throw new \Exception('Prospects ids count and prospects events count don\'t match!');
        }

        $data = [];

        foreach ($items as $index => $events) {
            $prospectId = $prospectsIds[$index];

            // $events: events to associate to $prospectId
            foreach ($events as $event) {
                $data[] = array_merge($event, [
                    'prospect_id' => $prospectId,
                    'creator_id'  => $import->creator_id,
                    'created_at'  => $date,
                    'updated_at'  => $date,
                ]);
            }
        }

        DB::table('events')->insert($data);
    }
}