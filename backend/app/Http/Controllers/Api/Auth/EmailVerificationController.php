<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\AuditService;
use App\Services\EmailVerificationService;

class EmailVerificationController extends Controller
{
    public function verify(Request $request)
    {
        $user = User::where('id', $request->id)
            ->first();

        if (!$user) {

            return response()->json([
                'message' => 'Usuario no encontrado.'
            ], 404);
        }

        $valid = EmailVerificationService::verify(
            $user,
            $request->token
        );

        if (!$valid) {

            return response()->json([
                'message' => 'Token inválido o expirado.'
            ], 400);
        }

        $user->update([

            'email_verified_at' => now(),

            'verification_token' => null,

            'verification_expires_at' => null
        ]);

        AuditService::log(
            $user->id,
            'EMAIL_VERIFICADO'
        );

        return response()->json([

            'success' => true,

            'message' => 'Correo verificado correctamente.'
        ]);
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at) {

            return response()->json([
                'message' => 'El correo ya está verificado.'
            ]);
        }

        $token = EmailVerificationService::generate($user);

        $url = config('app.frontend_url')
            . '/verify-email?token='
            . $token
            . '&id='
            . $user->id;

        \Mail::to($user->email)
            ->send(
                new \App\Mail\VerifyEmailMail(
                    $user,
                    $url
                )
            );

        AuditService::log(
            $user->id,
            'REENVIO_VERIFICACION_EMAIL'
        );

        return response()->json([

            'success' => true,

            'message' => 'Correo reenviado.'
        ]);
    }
}
