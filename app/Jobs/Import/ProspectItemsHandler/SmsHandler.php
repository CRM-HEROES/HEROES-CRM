<?php

namespace App\Jobs\Import\ProspectItemsHandler;

use App\Jobs\Import\ProspectItemsHandlerInterface;
use App\Models\Import;
use Illuminate\Support\Facades\DB;

class SmsHandler implements ProspectItemsHandlerInterface
{
    /**
     * 
     */
    public function handle(Import $import, &$prospectsIds, &$items, $date)
    {
        // check that $prospectsIds and $items arrays
        // have the same size
        if (count($prospectsIds) != count($items)) {
            throw new \Exception('Prospects ids count and prospects sms count don\'t match!');
        }

        $data = [];

        foreach ($items as $index => $sms) {
            $prospectId = $prospectsIds[$index];

            // $sms: sms to associate to $prospectId
            foreach ($sms as $s) {
                $data[] = array_merge($s, [
                    'prospect_id' => $prospectId,
                    'updated_at'  => $date,
                ]);
            }
        }

        DB::table('sms')->insert($data);
    }
}