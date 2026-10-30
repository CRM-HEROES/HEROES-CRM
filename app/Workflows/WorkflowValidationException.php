<?php

namespace App\Workflows;

use RuntimeException;

/**
 * A workflow cannot be published / activated. The messages are meant to be
 * shown to the user as they are.
 */
class WorkflowValidationException extends RuntimeException
{
    /**
     * @param  string[]  $errors
     */
    public function __construct(public readonly array $errors)
    {
        parent::__construct(implode("\n", $errors));
    }
}
