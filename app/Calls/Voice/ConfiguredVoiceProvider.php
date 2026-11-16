<?php

namespace App\Calls\Voice;

use App\Models\CallAttempt;
use App\Models\Prospect;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class ConfiguredVoiceProvider implements VoiceProvider
{
    public function __construct(protected Container $container)
    {
    }

    public function start(CallAttempt $attempt, Prospect $prospect, array $context = []): array
    {
        $provider = match ($attempt->provider ?: config('calls.provider', 'fake')) {
            'cloudtalk' => CloudTalkProvider::class,
            'fake' => FakeVoiceProvider::class,
            'ai_voice' => AiVoiceProvider::class,
            default => throw new InvalidArgumentException('Le fournisseur vocal configuré est inconnu.'),
        };

        return $this->container->make($provider)->start($attempt, $prospect, $context);
    }
}
