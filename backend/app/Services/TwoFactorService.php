<?php

namespace App\Services;

use OTPHP\TOTP;

class TwoFactorService
{
    public static function generateSecret()
    {
        return TOTP::create();
    }

    public static function verify(
        string $secret,
        string $code
    ): bool {

        $totp = TOTP::create($secret);

        return $totp->verify($code);
    }
}