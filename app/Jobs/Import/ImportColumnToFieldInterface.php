<?php

namespace App\Jobs\Import;

interface ImportColumnToFieldInterface
{
    /**
     * 
     */
    public function handle(&$prospect, $field, $value);
}