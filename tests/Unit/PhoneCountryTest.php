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

    public function test_it_detects_countries_worldwide_from_an_explicit_country_code(): void
    {
        $this->assertSame('US', PhoneCountry::detect('+12025551234'));
        $this->assertSame('MA', PhoneCountry::detect('+212612345678'));
        $this->assertSame('DE', PhoneCountry::detect('+491512345678'));
        $this->assertSame('CI', PhoneCountry::detect('+2250102030405'));
    }

    public function test_it_returns_null_for_a_local_number_outside_belgium_or_france(): void
    {
        // No country code and not a 9/10-digit BE/FR shape: undecidable.
        $this->assertNull(PhoneCountry::detect('020 7946 0958'));
    }

    public function test_it_detects_the_dial_code_for_belgium_and_france(): void
    {
        $this->assertSame('+32', PhoneCountry::detectDialCode('+32470123456'));
        $this->assertSame('+33', PhoneCountry::detectDialCode('+33612345678'));
        $this->assertSame('+33', PhoneCountry::detectDialCode('0612345678'));
    }

    public function test_it_returns_null_dial_code_when_the_number_is_unrecognized(): void
    {
        $this->assertNull(PhoneCountry::detectDialCode(null));
        $this->assertNull(PhoneCountry::detectDialCode(''));
        $this->assertNull(PhoneCountry::detectDialCode('0470123456'));
    }

    public function test_it_converts_a_region_code_to_its_dial_code(): void
    {
        $this->assertSame('+33', PhoneCountry::dialCodeForRegion('FR'));
        $this->assertSame('+32', PhoneCountry::dialCodeForRegion('BE'));
        $this->assertSame('+1', PhoneCountry::dialCodeForRegion('US'));
        $this->assertNull(PhoneCountry::dialCodeForRegion(null));
    }

    protected function userWithDialCodes(int $id, ?array $dialCodes): \App\Models\User
    {
        $user = new \App\Models\User(['phone_country' => $dialCodes]);
        $user->id = $id;

        return $user;
    }

    public function test_filter_users_by_dial_code_keeps_only_matching_users(): void
    {
        $france = $this->userWithDialCodes(1, ['+33']);
        $belgium = $this->userWithDialCodes(2, ['+32']);

        $result = PhoneCountry::filterUsersByDialCode(collect([$france, $belgium]), '+33');

        $this->assertSame([1], $result->pluck('id')->all());
    }

    public function test_filter_users_by_dial_code_keeps_users_with_no_country_configured(): void
    {
        $unconfigured = $this->userWithDialCodes(1, null);
        $franceOnly = $this->userWithDialCodes(2, ['+33']);

        $result = PhoneCountry::filterUsersByDialCode(collect([$unconfigured, $franceOnly]), '+32');

        $this->assertSame([1], $result->pluck('id')->all());
    }

    public function test_filter_users_by_dial_code_returns_everyone_when_dial_code_is_unknown(): void
    {
        $france = $this->userWithDialCodes(1, ['+33']);
        $belgium = $this->userWithDialCodes(2, ['+32']);

        $result = PhoneCountry::filterUsersByDialCode(collect([$france, $belgium]), null);

        $this->assertSame([1, 2], $result->pluck('id')->all());
    }

    public function test_filter_users_by_dial_code_falls_back_to_everyone_when_nobody_matches(): void
    {
        $franceA = $this->userWithDialCodes(1, ['+33']);
        $franceB = $this->userWithDialCodes(2, ['+33']);

        $result = PhoneCountry::filterUsersByDialCode(collect([$franceA, $franceB]), '+32');

        $this->assertSame([1, 2], $result->pluck('id')->all());
    }
}
