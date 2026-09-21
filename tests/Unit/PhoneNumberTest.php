<?php

namespace Tests\Unit;

use App\Support\PhoneNumber;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    public function test_it_keeps_digits_only(): void
    {
        $this->assertSame('33688753390', PhoneNumber::digits('+33 6 88 75 33 90'));
        $this->assertSame('0688753390', PhoneNumber::digits('06.88.75.33.90'));
        $this->assertSame('', PhoneNumber::digits(null));
    }

    /**
     * A French number is stored locally but presented internationally by the
     * PBX: both forms have to be matched, or an incoming call from a known
     * prospect looks like an unknown caller.
     */
    public function test_it_returns_both_the_local_and_international_forms(): void
    {
        $candidates = PhoneNumber::candidates('+33688753390');

        $this->assertContains('33688753390', $candidates);
        $this->assertContains('0688753390', $candidates);

        $candidates = PhoneNumber::candidates('0688753390');

        $this->assertContains('0688753390', $candidates);
        $this->assertContains('33688753390', $candidates);
    }

    public function test_it_accepts_the_international_access_prefix(): void
    {
        $candidates = PhoneNumber::candidates('0033688753390');

        $this->assertContains('0033688753390', $candidates);
        $this->assertContains('33688753390', $candidates);
        $this->assertContains('0688753390', $candidates);
    }

    /**
     * Another country than the French default must be understood too: a
     * Belgian international number is also written locally (0470...).
     */
    public function test_it_uses_the_detected_country_for_local_numbers(): void
    {
        $candidates = PhoneNumber::candidates('+32470123456');

        $this->assertContains('32470123456', $candidates);
        $this->assertContains('0470123456', $candidates);
    }

    public function test_it_returns_nothing_for_an_empty_number(): void
    {
        $this->assertSame([], PhoneNumber::candidates(''));
        $this->assertSame([], PhoneNumber::candidates(null));
    }

    public function test_it_builds_a_digits_sql_expression(): void
    {
        $expression = PhoneNumber::digitsExpression('phone_number');

        $this->assertStringContainsString("REPLACE(phone_number, ' ', '')", $expression);
        $this->assertStringContainsString("'-', ''", $expression);
        $this->assertStringContainsString("'+', ''", $expression);
    }
}
