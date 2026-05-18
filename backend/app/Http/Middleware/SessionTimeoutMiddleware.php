<?php

namespace App\Http\Middleware;

use Closure;

class SessionTimeoutMiddleware
{
    public function handle(
        $request,
        Closure $next
    ) {

        $user = $request->user();

        if ($user) {

            if (
                $user->last_activity_at &&
                now()->diffInMinutes(
                    $user->last_activity_at
                ) >= 30
            ) {

                $request->user()
                    ->currentAccessToken()
                    ->delete();

                return response()->json([

                    'message' =>
                        'Sesión expirada.'
                ], 401);
            }

            $user->update([
                'last_activity_at' => now()
            ]);
        }

        return $next($request);
    }
}