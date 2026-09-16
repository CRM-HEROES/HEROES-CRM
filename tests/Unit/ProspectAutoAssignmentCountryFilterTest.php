<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\ProspectAutoAssignment;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Covers the phone-indicatif routing rules in isolation: a user can be
 * configured with one or more dial codes (User::phone_country, a JSON
 * array such as ["+33", "+32"]), and a prospect should only reach users
 * configured for its number's dial code, falling back to the full pool
 * when either side has no country preference.
 */
class ProspectAutoAssignmentCountryFilterTest extends TestCase
{
    protected function filterUsersByCountry(Collection $users, ?string $dialCode): Collection
    {
        $service = new ProspectAutoAssignment();
        $method = (new ReflectionClass($service))->getMethod('filterUsersByCountry');
        $method->setAccessible(true);

        return $method->invoke($service, $users, $dialCode);
    }

    protected function makeUser(int $id, ?array $phoneCountry): User
    {
        $user = new User(['phone_country' => $phoneCountry]);
        $user->id = $id;

        return $user;
    }

    public function test_a_french_prospect_is_routed_only_to_users_configured_for_france(): void
    {
        $userFrance = $this->makeUser(1, ['+33']);
        $userBelgium = $this->makeUser(2, ['+32']);

        $result = $this->filterUsersByCountry(collect([$userFrance, $userBelgium]), '+33');

        $this->assertSame([1], $result->pluck('id')->all());
    }

    public function test_a_belgian_prospect_is_routed_only_to_users_configured_for_belgium(): void
    {
        $userFrance = $this->makeUser(1, ['+33']);
        $userBelgium = $this->makeUser(2, ['+32']);

        $result = $this->filterUsersByCountry(collect([$userFrance, $userBelgium]), '+32');

        $this->assertSame([2], $result->pluck('id')->all());
    }

    public function test_a_user_configured_for_both_countries_receives_both(): void
    {
        $bothCountries = $this->makeUser(1, ['+33', '+32']);
        $franceOnly = $this->makeUser(2, ['+33']);
        $users = collect([$bothCountries, $franceOnly]);

        $this->assertSame([1, 2], $this->filterUsersByCountry($users, '+33')->pluck('id')->all());
        $this->assertSame([1], $this->filterUsersByCountry($users, '+32')->pluck('id')->all());
    }

    public function test_a_prospect_with_no_detectable_number_is_eligible_for_every_user(): void
    {
        $userFrance = $this->makeUser(1, ['+33']);
        $userBelgium = $this->makeUser(2, ['+32']);

        $result = $this->filterUsersByCountry(collect([$userFrance, $userBelgium]), null);

        $this->assertSame([1, 2], $result->pluck('id')->all());
    }

    public function test_a_user_with_no_country_configured_has_no_preference(): void
    {
        $unconfigured = $this->makeUser(1, null);
        $franceOnly = $this->makeUser(2, ['+33']);

        $result = $this->filterUsersByCountry(collect([$unconfigured, $franceOnly]), '+32');

        $this->assertSame([1], $result->pluck('id')->all());
    }
}
