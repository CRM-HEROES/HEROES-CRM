<?php

namespace Tests\Support;

use App\Agents\Claude\ClaudeGateway;
use App\Agents\Claude\ClaudeGatewayFactory;
use App\Models\User;

/**
 * Gives a ready-made gateway instead of reading the key of the user.
 */
class ScriptedGatewayFactory extends ClaudeGatewayFactory
{
    public int $made = 0;

    public function __construct(protected ClaudeGateway $gateway)
    {
    }

    public function make(User $user): ClaudeGateway
    {
        $this->made++;

        return $this->gateway;
    }
}
