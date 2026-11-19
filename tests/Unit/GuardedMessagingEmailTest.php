<?php

namespace Tests\Unit;

use App\Calls\CallEligibility;
use App\Calls\GuardedMessaging;
use App\Calls\WorkingHours;
use App\Models\Project;
use App\Models\Prospect;
use Carbon\CarbonImmutable;
use Mockery;
use Tests\TestCase;

/**
 * When an e-mail of an agent is refused: the wish of the prospect and the working hours, not the switches of the calls.
 */
class GuardedMessagingEmailTest extends TestCase
{
    protected function guard(bool $open): GuardedMessaging
    {
        $hours = Mockery::mock(WorkingHours::class);
        $hours->shouldReceive('isOpen')->andReturn($open);
        $hours->shouldReceive('nextOpening')->andReturn(CarbonImmutable::parse('2026-11-20 08:00:00', 'UTC'));
        $hours->shouldReceive('timezone')->andReturn('UTC');

        return new GuardedMessaging(Mockery::mock(CallEligibility::class), $hours);
    }

    protected function prospect(bool $doNotCall = false): Prospect
    {
        $prospect = new Prospect();
        $prospect->do_not_call = $doNotCall;

        return $prospect;
    }

    public function test_an_email_is_allowed_whatever_the_call_switches_say(): void
    {
        // No database is set up here: the call switches (global kill switch, calls of the project) are not even read
        $this->assertNull($this->guard(true)->emailReason($this->prospect(), new Project()));
    }

    public function test_a_prospect_who_does_not_want_to_be_contacted_gets_no_email(): void
    {
        $this->assertSame('do_not_call', $this->guard(true)->emailReason($this->prospect(true), new Project()));
    }

    public function test_an_email_is_refused_outside_the_working_hours_with_the_next_opening(): void
    {
        $guard = $this->guard(false);

        $this->assertSame('outside_calling_hours', $guard->emailReason($this->prospect(), new Project()));
        $this->assertStringContainsString('20/11/2026 à 08:00', $guard->message('outside_calling_hours', $this->prospect(), 'email'));
    }

    public function test_the_messages_of_the_removed_switches_are_gone_for_email(): void
    {
        $message = $this->guard(true)->message('global_disabled', $this->prospect(), 'email');

        $this->assertSame('Le message ne peut pas être envoyé : global_disabled', $message);
    }
}
