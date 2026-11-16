<?php

namespace App\Calls;

use RuntimeException;

class SmsEligibilityException extends RuntimeException
{
    public function __construct(public readonly string $reason, string $message)
    {
        parent::__construct($message);
    }
}
