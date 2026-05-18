<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;

use Illuminate\Http\JsonResponse;

use App\Http\Controllers\Controller;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Services\AuditService;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class EmployeeController extends Controller
{
    public function index(): JsonResponse
    {
        $employees = User::role([
            'RECEPCION',
            'GROOMER'
        ])
            ->withTrashed()
            ->latest()
            ->get();

        return response()->json([
            'employees' => $employees
        ]);
    }

    public function store(
        StoreEmployeeRequest $request
    ): JsonResponse {

        // generar contraseña temporal
        $tempPassword = Str::random(10);

        $user = User::create([

            'nombre_completo' =>
            strip_tags($request->nombre_completo),

            'email' =>
            $request->email,

            'password' =>
            Hash::make($tempPassword),

            'telefono' =>
            $request->telefono,

            'ci' =>
            $request->ci,

            'turno' =>
            $request->turno,

            'is_active' => true,

            'email_verified_at' => now(),

            'password_change_required' => true
        ]);

        $user->assignRole($request->rol);

        AuditService::log(
            auth()->id(),
            'CREAR_EMPLEADO',
            'usuarios',
            $user->id,
            null,
            $user->toArray()
        );

        // enviar email
        Mail::send('emails.staff-created', [
            'user' => $user,
            'password' => $tempPassword
        ], function ($message) use ($user) {

            $message->to($user->email)
                ->subject('Credenciales de acceso - Pet Spa');
        });

        return response()->json([
            'message' =>
            'Empleado creado correctamente.',

            'employee' => $user
        ], 201);
    }

    public function update(
        UpdateEmployeeRequest $request,
        string $id
    ): JsonResponse {

        $user = User::findOrFail($id);

        $datosAntes = $user->toArray();

        $data = [

            'nombre_completo' =>
            strip_tags($request->nombre_completo),

            'email' =>
            $request->email,

            'telefono' =>
            $request->telefono,

            'ci' =>
            $request->ci,

            'turno' =>
            $request->turno,
        ];

        if ($request->filled('password')) {

            $data['password'] =
                $request->password;
        }

        $user->update($data);

        $user->syncRoles([
            $request->rol
        ]);

        AuditService::log(
            auth()->id(),
            'ACTUALIZAR_EMPLEADO',
            'usuarios',
            $user->id,
            $datosAntes,
            $user->fresh()->toArray()
        );

        return response()->json([
            'message' =>
            'Empleado actualizado correctamente.',

            'employee' => $user
        ]);
    }

    public function destroy(
        string $id
    ): JsonResponse {

        $user = User::findOrFail($id);

        $datosAntes = $user->toArray();

        $user->update([
            'is_active' => false
        ]);

        $user->delete();

        AuditService::log(
            auth()->id(),
            'ELIMINAR_EMPLEADO',
            'usuarios',
            $user->id,
            $datosAntes,
            null
        );

        return response()->json([
            'message' =>
            'Empleado desactivado correctamente.'
        ]);
    }

    public function restore(
        string $id
    ): JsonResponse {

        $user = User::withTrashed()
            ->findOrFail($id);

        $user->restore();

        $user->update([
            'is_active' => true
        ]);

        AuditService::log(
            auth()->id(),
            'RESTAURAR_EMPLEADO',
            'usuarios',
            $user->id,
            null,
            $user->toArray()
        );

        return response()->json([
            'message' =>
            'Empleado restaurado correctamente.',

            'employee' => $user
        ]);
    }
}
