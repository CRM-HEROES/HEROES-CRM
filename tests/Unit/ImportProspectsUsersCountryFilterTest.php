<?php

namespace Tests\Unit;

use App\Jobs\ImportProspects;
use App\Models\User;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Covers the "Utilisateurs affectés" import step: a prospect should only be
 * attached to the marked users configured for its phone dial code, falling
 * back to every marked user when the prospect has no number, or when none
 * of the marked users match its dial code.
 */
class ImportProspectsUsersCountryFilterTest extends TestCase
{
    protected function filterMarkedUsersByCountry(Collection $users, ?string $dialCode): Collection
    {
        $job = (new ReflectionClass(ImportProspects::class))->newInstanceWithoutConstructor();
        $method = (new ReflectionClass($job))->getMethod('filterMarkedUsersByCountry');
        $method->setAccessible(true);

        return $method->invoke($job, $users, $dialCode);
    }

    protected function makeUser(int $id, ?array $phoneCountry): User
    {
        $user = new User(['phone_country' => $phoneCountry]);
        $user->id = $id;

        return $user;
    }

    public function test_a_french_prospect_is_attached_only_to_marked_users_configured_for_france(): void
    {
        $userFrance = $this->makeUser(1, ['+33']);
        $userBelgium = $this->makeUser(2, ['+32']);

        $result = $this->filterMarkedUsersByCountry(collect([$userFrance, $userBelgium]), '+33');

        $this->assertSame([1], $result->pluck('id')->all());
    }

    public function test_a_belgian_prospect_is_attached_only_to_marked_users_configured_for_belgium(): void
    {
        $userFrance = $this->makeUser(1, ['+33']);
        $userBelgium = $this->makeUser(2, ['+32']);

        $result = $this->filterMarkedUsersByCountry(collect([$userFrance, $userBelgium]), '+32');

        $this->assertSame([2], $result->pluck('id')->all());
    }

    public function test_a_marked_user_configured_for_both_countries_is_attached_to_both_categories(): void
    {
        $bothCountries = $this->makeUser(1, ['+33', '+32']);
        $franceOnly = $this->makeUser(2, ['+33']);
        $markedUsers = collect([$bothCountries, $franceOnly]);

        $this->assertSame([1, 2], $this->filterMarkedUsersByCountry($markedUsers, '+33')->pluck('id')->all());
        $this->assertSame([1], $this->filterMarkedUsersByCountry($markedUsers, '+32')->pluck('id')->all());
    }

    public function test_a_prospect_with_no_phone_number_is_attached_to_every_marked_user(): void
    {
        $userFrance = $this->makeUser(1, ['+33']);
        $userBelgium = $this->makeUser(2, ['+32']);

        $result = $this->filterMarkedUsersByCountry(collect([$userFrance, $userBelgium]), null);

        $this->assertSame([1, 2], $result->pluck('id')->all());
    }

    public function test_a_marked_user_with_no_country_configured_is_attached_regardless_of_the_prospects_number(): void
    {
        $unconfigured = $this->makeUser(1, null);
        $franceOnly = $this->makeUser(2, ['+33']);

        $result = $this->filterMarkedUsersByCountry(collect([$unconfigured, $franceOnly]), '+32');

        $this->assertSame([1], $result->pluck('id')->all());
    }

    public function test_falls_back_to_every_marked_user_when_none_match_the_dial_code(): void
    {
        $franceOnly = $this->makeUser(1, ['+33']);
        $anotherFranceOnly = $this->makeUser(2, ['+33']);

        // No marked user is configured for +32: nobody would ever get this
        // lead if the pool were filtered strictly, so it falls back to the
        // full marked pool instead of being left unassigned.
        $result = $this->filterMarkedUsersByCountry(collect([$franceOnly, $anotherFranceOnly]), '+32');

        $this->assertSame([1, 2], $result->pluck('id')->all());
    }
}
