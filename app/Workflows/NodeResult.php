<?php

namespace App\Workflows;

use Carbon\CarbonInterface;

/**
 * What a node returns to the engine.
 */
class NodeResult
{
    public const COMPLETED = 'completed';
    public const FAILED = 'failed';
    // The subject does not exist anymore: stop without error
    public const CANCELLED = 'cancelled';
    // Not run: an external service is saturated, try again later
    public const DEFERRED = 'deferred';
    // Parked until an event happens (or the timeout is over)
    public const EVENT_WAIT = 'event_wait';

    protected function __construct(
        public string $status,
        public ?string $branch = null,
        public ?CarbonInterface $waitUntil = null,
        public bool $terminal = false,
        public array $data = [],
        public ?string $message = null,
        // A failure that does not stop the workflow ("on_error": "continue")
        public bool $continue = false,
        // EVENT_WAIT: the event the execution waits for
        public ?string $awaitedEvent = null,
    ) {
    }

    /**
     * Park the execution on this node until $event happens for the prospect,
     * or until $timeoutAt. The node decides the way (yes / no) when it is resumed.
     */
    public static function waitForEvent(string $event, CarbonInterface $timeoutAt, array $data = []): self
    {
        return new self(self::EVENT_WAIT, null, $timeoutAt, false, $data, null, false, $event);
    }

    /**
     * The node was NOT run: park the execution on it and try again at $until.
     */
    public static function deferred(CarbonInterface $until): self
    {
        return new self(self::DEFERRED, null, $until);
    }

    /**
     * The node is done: go on with the next one
     * (through the $branch edge for a condition).
     */
    public static function completed(array $data = [], ?string $branch = null): self
    {
        return new self(self::COMPLETED, $branch, null, false, $data);
    }

    /**
     * The node is done: go on with the next one, but not before $until.
     * Nothing is held meanwhile: the execution is stored as "waiting".
     */
    public static function waitUntil(CarbonInterface $until, array $data = []): self
    {
        return new self(self::COMPLETED, null, $until, false, $data);
    }

    /**
     * The node is done and the workflow stops here.
     */
    public static function end(array $data = []): self
    {
        return new self(self::COMPLETED, null, null, true, $data);
    }

    /**
     * @param  bool  $continue  record the failure but go on with the next node
     */
    public static function failed(string $message, array $data = [], bool $continue = false): self
    {
        return new self(self::FAILED, null, null, false, $data, $message, $continue);
    }

    public static function cancelled(string $message): self
    {
        return new self(self::CANCELLED, null, null, false, [], $message);
    }
}
