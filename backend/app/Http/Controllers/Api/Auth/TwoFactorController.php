<?php

namespace App\Http\Controllers\Api\Auth;

use OTPHP\TOTP;

use App\Services\AuditService;

use Illuminate\Http\Request;

use BaconQrCode\Renderer\ImageRenderer;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;

use BaconQrCode\Renderer\RendererStyle\RendererStyle;

use BaconQrCode\Writer;

use App\Http\Controllers\Controller;

class TwoFactorController extends Controller
{
    public function generate(Request $request)
    {
        $user = $request->user();


        $totp = \OTPHP\TOTP::create();


        $totp->setLabel($user->email);

        $totp->setIssuer('Pet Spa');

        $secret = $totp->getSecret();

        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        $qrCode = base64_encode(
            $writer->writeString(
                $totp->getProvisioningUri()
            )
        );

        $user->update([
            'two_factor_secret' => encrypt($secret)
        ]);

        AuditService::log(
            $user->id,
            '2FA_GENERATED'
        );

        return response()->json([

            'secret' => $secret,

            'qr_code' => $qrCode
        ]);
    }

    public function enable(Request $request)
    {
        $request->validate([

            'code' => [
                'required',
                'digits:6'
            ]
        ]);

        $user = $request->user();

        $secret = decrypt(
            $user->two_factor_secret
        );

        $totp = TOTP::create($secret);

        if (!$totp->verify($request->code)) {

            return response()->json([

                'message' => 'Código inválido.'
            ], 400);
        }

        $backupCodes = [];

        for ($i = 0; $i < 8; $i++) {

            $backupCodes[] =
                strtoupper(
                    \Str::random(10)
                );
        }

        $user->update([

            'two_factor_enabled' => true,

            'backup_codes' => $backupCodes
        ]);

        AuditService::log(
            $user->id,
            '2FA_ENABLED'
        );

        return response()->json([

            'success' => true,

            'backup_codes' => $backupCodes
        ]);
    }

    public function disable(Request $request)
    {
        $user = $request->user();

        $user->update([

            'two_factor_enabled' => false,

            'two_factor_secret' => null,

            'backup_codes' => null
        ]);

        AuditService::log(
            $user->id,
            '2FA_DISABLED'
        );

        return response()->json([

            'success' => true,

            'message' => '2FA desactivado.'
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([

            'email' => 'required|email',

            'code' => 'required'
        ]);

        $user = \App\Models\User::where(
            'email',
            $request->email
        )->first();

        if (!$user) {

            return response()->json([

                'message' => 'Usuario no encontrado.'
            ], 404);
        }

        if (!$user->two_factor_enabled) {

            return response()->json([

                'message' => '2FA no habilitado.'
            ], 400);
        }

        $valid = false;

        $secret = decrypt(
            $user->two_factor_secret
        );

        $totp = TOTP::create($secret);

        if ($totp->verify($request->code)) {

            $valid = true;
        }

        if (
            !$valid &&
            in_array(
                $request->code,
                $user->backup_codes ?? []
            )
        ) {

            $codes = collect(
                $user->backup_codes
            )
                ->reject(
                    fn($c) =>
                    $c === $request->code
                )
                ->values()
                ->toArray();

            $user->update([
                'backup_codes' => $codes
            ]);

            $valid = true;
        }

        if (!$valid) {

            return response()->json([

                'message' => 'Código inválido.'
            ], 400);
        }

        $token = $user
            ->createToken('2fa_auth')
            ->plainTextToken;

        AuditService::log(
            $user->id,
            '2FA_LOGIN_SUCCESS'
        );

        return response()->json([

            'success' => true,

            'token' => $token,

            'user' => $user
        ]);
    }
}
