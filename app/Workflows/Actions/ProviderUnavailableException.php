<?php

namespace App\Workflows\Actions;

use RuntimeException;

/**
 * The operator of an action is not configured (or not active) in the
 * project anymore.
 */
class ProviderUnavailableException extends RuntimeException
{
}
