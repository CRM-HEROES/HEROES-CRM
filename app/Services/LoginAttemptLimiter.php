<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\UserLoginBlocked;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class LoginAttemptLimiter
{
    private const MAX_FAILURES = 3;

    public function recordFailure(mixed $email): void
    {
        if (!is_string($email) || $email === '') {
            return;
        }

        $blockedUser = DB::transaction(function () use ($email) {
            $user = User::query()
                ->where('email', $email)
                ->whereNull('banned_at')
                ->lockForUpdate()
                ->first();

            if (!$user) {
                return null;
            }

            $user->failed_login_attempts++;

            if ($user->failed_login_attempts >= self::MAX_FAILURES) {
                $user->banned_at = now();
                $user->save();

                return $user;
            }

            $user->save();

            return null;
        });

        if (!$blockedUser) {
            return;
        }

        try {
            Notification::send($blockedUser, new UserLoginBlocked());
        } catch (Throwable $exception) {
            Log::error('Failed to send login block notification.', [
                'user_id' => $blockedUser->id,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    public function reset(User $user): void
    {
        if ($user->failed_login_attempts === 0) {
            return;
        }

        $user->failed_login_attempts = 0;
        $user->save();
    }
}
