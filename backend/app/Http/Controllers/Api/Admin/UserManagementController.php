<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use App\Http\Controllers\Controller;
use App\Services\AuditService;

class UserManagementController extends Controller
{
    public function createStaff(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'rol' => 'required|in:RECEPCION,GROOMER,ADMIN',
            'telefono' => 'nullable|string|max:20',
            'turno' => 'nullable|string|in:MAÑANA,TARDE,NOCHE'
        ]);

        return DB::transaction(function () use ($request) {

            // 1. password temporal
            $tempPassword = Str::random(10);

            // 2. crear usuario
            $user = User::create([
                'nombre_completo' => $request->nombre_completo,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'turno' => $request->turno,
                'password' => Hash::make($tempPassword),
                'email_verified_at' => now(),
                'is_active' => true,
                'password_change_required' => true
            ]);

            // 3. asignar rol
            $user->assignRole($request->rol);

            AuditService::log(
                auth()->id(),
                'CREAR_PERSONAL_' . $request->rol,
                'usuarios',
                $user->id,
                null,
                $user->toArray()
            );

            Mail::send('emails.staff-created', [
                'user' => $user,
                'password' => $tempPassword
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Credenciales de acceso - Pet Spa');
            });

            return response()->json([
                'success' => true,
                'message' => 'Personal creado correctamente',
                'data' => $user
            ]);
        });
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ]
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta.'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
            'password_change_required' => false
        ]);

        AuditService::log(
            $user->id,
            'CAMBIAR_CONTRASEÑA',
            'usuarios',
            $user->id
        );

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.'
        ]);
    }
}
