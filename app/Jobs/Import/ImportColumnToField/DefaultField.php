<?php

namespace App\Jobs\Import\ImportColumnToField;

use App\Jobs\Import\ImportColumnToFieldInterface;

class DefaultField implements ImportColumnToFieldInterface
{
    protected $limitedDefaultFieldsSize = ['postal_code', 'phone_number', 'mobile_phone_number'];

    protected function formatDateValue($value)
    {
        $value = trim(str_replace("/", "-", $value));

        if (
            preg_match(
                '/^(\\d{1,2})-(\\d{1,2})-(\\d{2,4})(.*)/', 
                $value, 
                $matches, 
                PREG_OFFSET_CAPTURE
            )
        ) {
            // The first number is the day, the second is the month, and the third is the year
            if ($matches[2][0] > 12) {
                $day = $matches[2][0];
                $matches[2][0] = $matches[1][0];
                $matches[1][0] = $day;
            }
            
            // If the day or month is a single digit, add a leading zero
            if (strlen($matches[1][0]) == 1) {
                $matches[1][0] = '0' . $matches[1][0];
            }
            
            if (strlen($matches[2][0]) == 1) {
                $matches[2][0] = '0' . $matches[2][0];
            }

            // If the year is two digits, add the century
            if (strlen($matches[3][0]) == 2) {
                $matches[3][0] = '20' . $matches[3][0];
            }

            $value = 
                $matches[3][0] . '-' . 
                $matches[2][0] . '-' . 
                $matches[1][0] . 
                $matches[4][0];
        }

        // If the time is not specified, add it
        if (strlen($value) == 10) {
            $value .= ' 00:00:00';
        } else if (strlen($value) == 13) {
            $value .= ':00:00';
        } else if (strlen($value) == 16) {
            $value .= ':00';
        }

        return $value;
    }

    /**
     * 
     */
    public function handle(&$prospect, $field, $value)
    {
        if (in_array($field, $this->limitedDefaultFieldsSize)) {
            $value = mb_substr($value, 0, 20);
        } else {
            $value = mb_substr($value, 0, 191);
        }

        if ($field == 'created_at') {
            if (empty($value)) {
                return;
            }

            $value = $this->formatDateValue($value);
        }

        $prospect[$field] = !empty($value) ? $value : null;
    }
}