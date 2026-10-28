<?php

namespace App\Exceptions;

use Symfony\Component\Mailer\Exception\TransportException;

class BrevoTransportException extends TransportException
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
