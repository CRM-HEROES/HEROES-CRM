<?php

namespace App\Agents\Claude;

use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;

/**
 * Builds the gateway of a user from the Claude API key they saved in the CRM
 * settings (users.claude_api_key). There is no platform key: an agent runs on
 * the key of its owner.
 */
class ClaudeGatewayFactory
{
    /**
     * @throws ClaudeException when the user has no usable key
     */
    public function make(User $user): ClaudeGateway
    {
        try {
            $key = $user->claude_api_key;
        } catch (DecryptException) {
            // Saved with another APP_KEY: it must be entered again
            throw ClaudeException::missingKey();
        }

        if (!filled($key)) {
            throw ClaudeException::missingKey();
        }

        return new AnthropicGateway($key);
    }
}
