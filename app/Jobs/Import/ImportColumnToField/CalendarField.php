<?php

namespace App\Jobs\Import\ImportColumnToField;

use App\Jobs\Import\ImportColumnToFieldInterface;

class CalendarField implements ImportColumnToFieldInterface
{
    protected $affectedUserId;
    
    public function __construct($affectedUserId)
    {
        $this->affectedUserId = $affectedUserId;
    }

    /**
     * 
     */
    public function handle(&$prospect, $field, $value)
    {
        try {
            if (empty($value)) {
                return;
            }

            $value = str_replace("/", "-", $value);

            if (
                preg_match(
                    '/^(\\d{1,2})-(\\d{1,2})-(\\d{4})/', 
                    $value, 
                    $matches, 
                    PREG_OFFSET_CAPTURE
                )
            ) {
                if (strlen($matches[1][0]) == 1) {
                    $matches[1][0] = '0' . $matches[1][0];
                }
                
                if (strlen($matches[2][0]) == 1) {
                    $matches[2][0] = '0' . $matches[2][0];
                }

                $value = 
                    $matches[3][0] . '-' . 
                    $matches[2][0] . '-' . 
                    $matches[1][0] . 
                    substr($value, 10);
            }

            if (strlen($value) == 10) {
                $value .= ' 08:00:00';
            }

            if (strlen($value) == 16) {
                $value .= ':00';
            }

            // Create a Carbon instance from the given value
            $carbonDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value);
            // Add 1 hour
            $carbonDate->addHour();
            // Format to Y-m-d h:i:s
            $endDate = $carbonDate->format('Y-m-d h:i:s');

            $prospect['events'][] = [
                'name'        => "",
                'description' => "",
                'started_at'  => $value,
                'ended_at'    => $endDate,
                'location'    => "",
                'user_id'     => $this->affectedUserId,
                'calendar_id' => $field->id,
            ];
        } catch (\Exception $e) {}
    }
}