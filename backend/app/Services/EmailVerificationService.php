<?php

namespace App\Services;

use App\Models\User;

use Illuminate\Support\Str;

class EmailVerificationService
{
    public static function generate(User $user)
    {
        $token = Str::random(64);

        $user->update([

            'verification_token' => $token,

            'verification_expires_at' => now()->addMinutes(15)
        ]);
        $user->refresh();

        return $token;
    }

    public static function verify(User $user, string $token)
    {
        if (!$user->verification_token) {
            return false;
        }

        if ($user->verification_token !== $token) {
            return false;
        }

        if (
            !$user->verification_expires_at ||
            now()->gt($user->verification_expires_at)
        ) {
            return false;
        }

        return true;
    }
}