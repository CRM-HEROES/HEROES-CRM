<?php

namespace Tests\Unit;

use App\Support\PhoneCountry;
use PHPUnit\Framework\TestCase;

class PhoneCountryTest extends TestCase
{
    public function test_it_detects_belgium_from_an_explicit_country_code(): void
    {
        $this->assertSame('BE', PhoneCountry::detect('+32470123456'));
        $this->assertSame('BE', PhoneCountry::detect('0032470123456'));
    }

    public function test_it_detects_france_from_an_explicit_country_code(): void
    {
        $this->assertSame('FR', PhoneCountry::detect('+33612345678'));
        $this->assertSame('FR', PhoneCountry::detect('0033612345678'));
    }

    public function test_it_detects_belgium_from_a_nine_digit_local_number(): void
    {
        $this->assertSame('BE', PhoneCountry::detect('021234567'));
    }

    public function test_it_detects_france_from_a_local_mobile_prefix(): void
    {
        $this->assertSame('FR', PhoneCountry::detect('0612345678'));
        $this->assertSame('FR', PhoneCountry::detect('0712345678'));
    }

    public function test_it_treats_a_ten_digit_zero_four_number_as_ambiguous(): void
    {
        $this->assertNull(PhoneCountry::detect('0470123456'));
    }

    public function test_it_detects_france_from_other_ten_digit_prefixes(): void
    {
        $this->assertSame('FR', PhoneCountry::detect('0123456789'));
    }

    public function test_it_returns_null_for_empty_or_unrecognized_numbers(): void
    {
        $this->assertNull(PhoneCountry::detect(null));
        $this->assertNull(PhoneCountry::detect(''));
        $this->assertNull(PhoneCountry::detect('+15551234567'));
        $this->assertNull(PhoneCountry::detect('12345'));
    }
}
