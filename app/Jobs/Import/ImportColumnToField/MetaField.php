<?php

namespace App\Jobs\Import\ImportColumnToField;

use App\Jobs\Import\ImportColumnToFieldInterface;

class MetaField implements ImportColumnToFieldInterface
{
    /**
     * 
     */
    public function handle(&$prospect, $field, $value)
    {
        $prospect['meta'][$field] = $value;
    }
}