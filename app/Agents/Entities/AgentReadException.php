<?php

namespace App\Agents\Entities;

/**
 * A read the CRM refuses or cannot understand. The message is written for the
 * model: it says what is wrong and what is possible, so that it can correct
 * itself.
 */
class AgentReadException extends \RuntimeException
{
    public function __construct(string $message, public readonly string $errorCode = 'INVALID_INPUT')
    {
        parent::__construct($message);
    }
}
