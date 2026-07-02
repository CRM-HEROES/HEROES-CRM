<?php

namespace App\Filters;

Trait TraitRequestFilters
{
    /**
     */
    public function addRequestFilters($filters) {
        try {
            $paramFilters = json_decode(request()->input('filters', "[]"), true);

            foreach ($paramFilters as $filter => $value) {
                if (in_array($filter, $filters)) {
                    $this->addFilter($filter, $value);
                }
            }
        } catch (\Exception $e) {
            \Log::debug($e);
        }
    }
}
