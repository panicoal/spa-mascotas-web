<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use App\Services\AuditService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;


class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    public function callback()
    {
        try {

            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $user = User::where('email', $googleUser->email)
                ->first();

            if (!$user) {

                $user = User::create([

                    'nombre_completo' => $googleUser->name,

                    'email' => $googleUser->email,

                    'google_id' => $googleUser->id,

                    'avatar_url' => $googleUser->avatar,

                    'email_verified_at' => now(),

                    'password' => Hash::make(
                        Str::random(32)
                    ),

                    'is_active' => true
                ]);

                $user->assignRole('CLIENTE');

                AuditService::log(
                    $user->id,
                    'REGISTRO_GOOGLE'
                );

            } else {

                if (!$user->google_id) {

                    $user->update([
                        'google_id' => $googleUser->id
                    ]);
                }

                AuditService::log(
                    $user->id,
                    'LOGIN_GOOGLE'
                );
            }

            $token = $user
                ->createToken('google_auth')
                ->plainTextToken;

            $frontendUrl =
                config('app.frontend_url')
                . '/oauth-success?token='
                . $token;

            return redirect($frontendUrl);

        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' => 'Error autenticando con Google.',

                'error' => $e->getMessage()
            ], 500);
        }
    }
}
