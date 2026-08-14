<?php

namespace Tests\Unit;

use App\Services\ProspectCallScorer;
use PHPUnit\Framework\TestCase;

class ProspectCallScorerTest extends TestCase
{
    public function test_it_classifies_a_high_conversion_probability_as_hot(): void
    {
        $result = (new ProspectCallScorer())->score(['conversion_probability' => 75]);

        $this->assertSame(['score' => 75, 'qualification' => 'hot', 'conversion_probability' => 75], $result);
    }

    public function test_it_uses_interest_and_urgency_when_probability_is_missing(): void
    {
        $result = (new ProspectCallScorer())->score([
            'interest_level' => 'medium',
            'urgency' => 'immediate',
        ]);

        $this->assertSame(60, $result['score']);
        $this->assertSame('warm', $result['qualification']);
    }
}
