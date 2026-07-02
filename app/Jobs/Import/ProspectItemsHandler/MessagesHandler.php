<?php

namespace App\Jobs\Import\ProspectItemsHandler;

use App\Jobs\Import\ProspectItemsHandlerInterface;
use App\Models\Import;
use Illuminate\Support\Facades\DB;

class MessagesHandler implements ProspectItemsHandlerInterface
{
    /**
     * 
     */
    public function handle(Import $import, &$prospectsIds, &$items, $date)
    {
        // check that $prospectsIds and $items arrays
        // have the same size
        if (count($prospectsIds) != count($items)) {
            throw new \Exception('Prospects ids count and prospects messages count don\'t match!');
        }

        $data = [];

        foreach ($items as $index => $messages) {
            $prospectId = $prospectsIds[$index];

            // $messages: messages to associate to $prospectId
            foreach ($messages as $message) {
                $data[] = array_merge($message, [
                    'prospect_id' => $prospectId,
                    'updated_at'  => $date,
                ]);
            }
        }

        DB::table('messages')->insert($data);
    }
}