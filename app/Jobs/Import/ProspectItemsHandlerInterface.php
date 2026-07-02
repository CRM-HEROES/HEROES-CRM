<?php

namespace App\Jobs\Import;

use App\Models\Import;

interface ProspectItemsHandlerInterface
{
    /**
     * 
     */
    public function handle(Import $import, &$prospectsIds, &$items, $date);
}