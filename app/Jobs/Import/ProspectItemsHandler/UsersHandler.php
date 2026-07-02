<?php

namespace App\Jobs\Import\ProspectItemsHandler;

use App\Jobs\Import\ProspectItemsHandlerInterface;
use App\Models\Import;
use Illuminate\Support\Facades\DB;

class UsersHandler implements ProspectItemsHandlerInterface
{
    /**
     * 
     */
    public function handle(Import $import, &$prospectsIds, &$items, $date)
    {
        // check that $prospectsIds and $items arrays
        // have the same size
        if (count($prospectsIds) != count($items)) {
            throw new \Exception('Prospects ids count and prospects users count don\'t match!');
        }

        $data = [];

        foreach ($items as $index => $users) {
            $prospectId = $prospectsIds[$index];

            // $users: users to associate to $prospectId
            foreach ($users as $user) {
                $data[] = [
                    'prospect_id' => $prospectId,
                    'user_id'     => $user,
                    'created_at'  => $date,
                    'updated_at'  => $date,
                ];
            }
        }

        DB::table('prospect_user')->insert($data);
    }
}