<?php

namespace App\Workflows\Legacy;

use RuntimeException;

/**
 * A campaign cannot be converted (or the result is not a valid workflow).
 * The messages are meant to be shown as they are.
 */
class LegacyConversionException extends RuntimeException
{
    /**
     * @param  string[]  $reasons
     */
    public function __construct(public readonly array $reasons)
    {
        parent::__construct(implode("\n", $reasons));
    }
}
