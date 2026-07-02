<?php

namespace App\Jobs\Import\ImportColumnToField;

use App\Jobs\Import\ImportColumnToFieldInterface;

class LinkField implements ImportColumnToFieldInterface
{
    /**
     * 
     */
    public function handle(&$prospect, $field, $value)
    {
        try {
            $data = json_decode($value, true);

            if (!is_array($data)) {
                return;
            }

            $prospect['links'] = array_map(function($link) {
                return [
                    'name' => data_get($link, 'name', ""),
                    'link' => data_get($link, 'link', ""),
                ];
            }, $data);
        } catch (\Exception $e) {}
    }
}