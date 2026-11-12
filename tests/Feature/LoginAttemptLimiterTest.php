<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\UserLoginBlocked;
use App\Services\LoginAttemptLimiter;
use App\Http\Middleware\UserTrackActivity;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Mockery;
use RuntimeException;
use Tests\Support\UsesInMemoryDatabase;
use Tests\TestCase;

class LoginAttemptLimiterTest extends TestCase
{
    use UsesInMemoryDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpInMemoryDatabase();
        $this->withoutMiddleware(UserTrackActivity::class);
        Event::fake([Authenticated::class]);
        Notification::fake();
    }

    public function test_api_login_blocks_after_three_failures_and_notifies_once(): void
    {
        $user = $this->makeLoginUser();

        $firstResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'incorrect',
        ]);
        $firstResponse->assertStatus(404);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'incorrect',
        ])->assertExactJson($firstResponse->json());

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'incorrect',
        ])->assertExactJson($firstResponse->json());

        $user->refresh();
        $this->assertSame(3, $user->failed_login_attempts);
        $this->assertNotNull($user->banned_at);
        Notification::assertSentTo($user, UserLoginBlocked::class, 1);
    }

    public function test_api_login_keeps_the_same_response_for_an_unknown_email(): void
    {
        $user = $this->makeLoginUser();

        $knownEmailResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'incorrect',
        ]);
        $unknownEmailResponse = $this->postJson('/api/login', [
            'email' => 'unknown@example.test',
            'password' => 'incorrect',
        ]);

        $this->assertSame($knownEmailResponse->json(), $unknownEmailResponse->json());
        $this->assertSame(1, $user->fresh()->failed_login_attempts);
        $this->assertDatabaseMissing('users', [
            'email' => 'unknown@example.test',
        ]);
    }

    public function test_web_login_blocks_after_three_failures(): void
    {
        $user = $this->makeLoginUser();

        $responses = [];
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $responses[] = $this->postJson('/login', [
                'email' => $user->email,
                'password' => 'incorrect',
            ]);
        }

        $this->assertSame(422, $responses[0]->getStatusCode());
        $this->assertSame($responses[0]->json(), $responses[1]->json());
        $this->assertSame($responses[0]->json(), $responses[2]->json());
        $this->assertSame(3, $user->fresh()->failed_login_attempts);
        $this->assertNotNull($user->fresh()->banned_at);
    }

    public function test_reset_clears_the_failed_attempt_counter(): void
    {
        $user = $this->makeLoginUser();
        $user->failed_login_attempts = 2;
        $user->save();

        app(LoginAttemptLimiter::class)->reset($user);

        $this->assertSame(0, $user->fresh()->failed_login_attempts);
    }

    public function test_notification_failure_is_logged_without_failing_the_limiter(): void
    {
        $user = $this->makeLoginUser();
        $user->failed_login_attempts = 2;
        $user->save();

        Notification::shouldReceive('send')
            ->once()
            ->andThrow(new RuntimeException('Mail transport unavailable'));
        Log::shouldReceive('error')
            ->once()
            ->with(
                'Failed to send login block notification.',
                Mockery::on(fn (array $context) => $context['user_id'] === $user->id
                    && $context['exception'] === 'Mail transport unavailable')
            );

        app(LoginAttemptLimiter::class)->recordFailure($user->email);

        $this->assertNotNull($user->fresh()->banned_at);
    }

    private function makeLoginUser(): User
    {
        return User::create([
            'name' => 'Login Test',
            'email' => uniqid('login-', true) . '@example.test',
            'password' => bcrypt('correct-password'),
        ]);
    }
}
