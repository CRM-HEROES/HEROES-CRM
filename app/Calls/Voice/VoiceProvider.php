<?php

namespace App\Calls\Voice;

use App\Models\CallAttempt;
use App\Models\Prospect;

interface VoiceProvider
{
    public function start(CallAttempt $attempt, Prospect $prospect, array $context = []): array;
}
