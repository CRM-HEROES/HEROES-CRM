<?php

namespace App\Agents;

/**
 * A request to an agent that cannot be accepted. The message can be shown to
 * the user.
 */
class AgentRequestException extends \RuntimeException
{
}
