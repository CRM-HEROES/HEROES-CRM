<?php

namespace App\Agents;

/**
 * The owner of an agent has no (more) access to the project of the run. It
 * stops the run: it is not an error of a tool that the model could work around.
 */
class AgentAccessException extends \RuntimeException
{
}
