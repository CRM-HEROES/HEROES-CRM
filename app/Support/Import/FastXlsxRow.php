<?php

namespace App\Support\Import;

class FastXlsxRow
{
    /** @var array Cell values, in column order */
    public $values;

    public function __construct(array $values)
    {
        $this->values = array_values($values);
    }
}
