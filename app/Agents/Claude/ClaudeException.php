<?php

namespace App\Agents\Claude;

/**
 * A problem with the Claude API, with a message that can be shown to the user
 * (never the raw error of the API: it can contain details of the request).
 */
class ClaudeException extends \RuntimeException
{
    public const MISSING_KEY = 'missing_key';
    public const INVALID_KEY = 'invalid_key';
    public const RATE_LIMITED = 'rate_limited';
    public const OVERLOADED = 'overloaded';
    public const TIMEOUT = 'timeout';
    public const UNAVAILABLE = 'unavailable';
    public const BAD_REQUEST = 'bad_request';
    public const FAILED = 'failed';

    public function __construct(
        public readonly string $reason,
        string $message,
        public readonly bool $retryable = false,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function missingKey(): self
    {
        return new self(self::MISSING_KEY, "Aucune clé API Claude n'est enregistrée pour le propriétaire de cet agent. Ajoutez-la dans les paramètres Claude.");
    }

    public static function invalidKey(?\Throwable $previous = null): self
    {
        return new self(self::INVALID_KEY, "La clé API Claude du propriétaire de l'agent est refusée. Vérifiez-la dans les paramètres Claude.", false, $previous);
    }

    public static function rateLimited(?\Throwable $previous = null): self
    {
        return new self(self::RATE_LIMITED, "La limite de requêtes de l'API Claude est atteinte. Réessayez dans quelques instants.", true, $previous);
    }

    public static function overloaded(?\Throwable $previous = null): self
    {
        return new self(self::OVERLOADED, "L'API Claude est temporairement surchargée. Réessayez dans quelques instants.", true, $previous);
    }

    public static function timeout(?\Throwable $previous = null): self
    {
        return new self(self::TIMEOUT, "L'API Claude n'a pas répondu à temps.", true, $previous);
    }

    public static function unavailable(?\Throwable $previous = null): self
    {
        return new self(self::UNAVAILABLE, "Impossible de joindre l'API Claude.", true, $previous);
    }

    public static function badRequest(?\Throwable $previous = null): self
    {
        return new self(self::BAD_REQUEST, "L'API Claude a refusé la demande de l'agent (configuration invalide).", false, $previous);
    }

    public static function failed(?\Throwable $previous = null): self
    {
        return new self(self::FAILED, "L'API Claude a renvoyé une erreur.", false, $previous);
    }
}
