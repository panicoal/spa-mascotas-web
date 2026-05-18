<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si el usuario debe cambiar contraseña y no es admin
        if ($user && $user->password_change_required && !$user->hasRole('ADMIN')) {
            // Permitir acceso al endpoint de cambiar contraseña
            if (!$request->is('api/users/change-password')) {
                return response()->json([
                    'message' => 'Debe cambiar su contraseña antes de continuar.',
                    'requires_password_change' => true,
                    'code' => 'PASSWORD_CHANGE_REQUIRED'
                ], 403);
            }
        }

        return $next($request);
    }
}


