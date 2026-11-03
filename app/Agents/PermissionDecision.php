<?php

namespace App\Agents;

/**
 * Outcome of PermissionGate for one tool call.
 */
final class PermissionDecision
{
    public const ALLOW = 'allow';
    public const APPROVE = 'approve';
    public const DENY = 'deny';

    private function __construct(
        public readonly string $outcome,
        public readonly string $reason,
    ) {
    }

    /** The tool runs now */
    public static function allow(string $reason = ''): self
    {
        return new self(self::ALLOW, $reason);
    }

    /** The call is kept as an action a human approves */
    public static function approve(string $reason): self
    {
        return new self(self::APPROVE, $reason);
    }

    /** The call is refused; the reason is given to the model */
    public static function deny(string $reason): self
    {
        return new self(self::DENY, $reason);
    }

    public function isAllowed(): bool
    {
        return $this->outcome === self::ALLOW;
    }

    public function needsApproval(): bool
    {
        return $this->outcome === self::APPROVE;
    }

    public function isDenied(): bool
    {
        return $this->outcome === self::DENY;
    }
}
