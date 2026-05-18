<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

use App\Services\AuditService;

use App\Http\Controllers\Controller;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;

use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmailMail;
use App\Services\EmailVerificationService;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'nombre_completo' => $request->nombre_completo,
            'email' => strtolower($request->email),
            'password' => $request->password,
            'telefono' => $request->telefono,
            'ci' => $request->ci
        ]);
        $user->assignRole('CLIENTE');

        $tokenVerification =
            EmailVerificationService::generate($user);

        $url = config('app.frontend_url')
            . '/verify-email?token='
            . $tokenVerification
            . '&id='
            . $user->id;

        Mail::to($user->email)
            ->send(
                new VerifyEmailMail(
                    $user,
                    $url
                )
            );

        $token = $user->createToken('auth_token')->plainTextToken;

        AuditService::log(
            $user->id,
            'REGISTRO_USUARIO',
            'usuarios',
            $user->id
        );

        return response()->json([

            'success' => true,
            'message' => 'Usuario registrado correctamente.',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where(
            'email',
            strtolower($request->email)
        )->first();

        if (!$user) {

            return response()->json([
                'message' => 'Credenciales inválidas.'
            ], 401);
        }

        if (
            $user->locked_until &&
            now()->lt($user->locked_until)
        ) {

            return response()->json([
                'message' =>
                'Cuenta bloqueada temporalmente.'
            ], 423);
        }

        if (!Hash::check($request->password, $user->password)) {

            $user->increment('failed_login_attempts');

            $user->refresh();

            $maxAttempts = 5;

            if ($user->failed_login_attempts >= $maxAttempts) {

                $user->update([
                    'locked_until' => now()->addMinutes(15)
                ]);

                AuditService::log(
                    $user->id,
                    'CUENTA_BLOQUEADA'
                );

                return response()->json([

                    'message' =>
                    'Cuenta bloqueada temporalmente por múltiples intentos fallidos.'
                ], 423);
            }

            AuditService::log(
                $user->id,
                'LOGIN_FALLIDO'
            );

            return response()->json([
                'message' => 'Credenciales inválidas.'
            ], 401);
        }

        $user->update([

            'failed_login_attempts' => 0,

            'locked_until' => null,

            'last_login' => now()
        ]);

        if (!$user->email_verified_at) {

            return response()->json([
                'message' => 'Debes verificar tu correo.'
            ], 403);
        }

        if ($user->two_factor_enabled) {

            return response()->json([

                'requires_2fa' => true,

                'message' => 'Ingrese código 2FA.',

                'email' => $user->email
            ]);
        }

        if (
            $user->hasRole('ADMIN') &&
            !$user->two_factor_enabled
        ) {

            $token = $user
                ->createToken('2fa_setup')
                ->plainTextToken;

            AuditService::log(
                $user->id,
                'ADMIN_REQUIERE_CONFIG_2FA'
            );

            return response()->json([

                'success' => true,

                'requires_2fa_setup' => true,

                'message' =>
                'Debe configurar autenticación en dos pasos.',

                'token' => $token,

                'user' => $user
            ]);
        }

        $token = $user
            ->createToken('auth_token')
            ->plainTextToken;

        AuditService::log(
            $user->id,
            'LOGIN_EXITOSO'
        );

        return response()->json([

            'success' => true,

            'token' => $token,

            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        AuditService::log(
            $request->user()->id,
            'LOGOUT'
        );

        $request->user()
            ->currentAccessToken()
            ->delete();

        return response()->json([

            'success' => true,

            'message' => 'Sesión cerrada.'
        ]);
    }
}
