<?php

namespace App\Http\Controllers\Api;

use App\Models\Pet;

use Illuminate\Http\JsonResponse;

use App\Http\Controllers\Controller;

use App\Http\Requests\StorePetRequest;

use App\Http\Requests\UpdatePetRequest;

use App\Services\AuditService;

class PetController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $pets = Pet::query()

            ->when(
                !$user->hasRole('ADMIN'),

                fn($query) => $query->where(
                    'cliente_id',
                    $user->id
                )
            )

            ->latest()

            ->get();

        return response()->json([

            'pets' => $pets
        ]);
    }

    public function store(
        StorePetRequest $request
    ): JsonResponse {

        $pet = Pet::create([

            'user_id' => auth()->id(),

            'nombre' =>
                strip_tags($request->nombre),

            'especie' =>
                $request->especie,

            'raza' =>
                strip_tags($request->raza),

            'sexo' =>
                $request->sexo,

            'tamanio' =>
                $request->tamanio ?? 'MEDIANO',

            'edad' =>
                $request->edad,

            'unidad_edad' =>
                $request->unidad_edad ?? 'MESES',

            'fecha_nacimiento' =>
                $request->fecha_nacimiento,

            'peso' =>
                $request->peso,

            'color' =>
                strip_tags($request->color),

            'caracteristicas_fisicas' =>
                strip_tags($request->caracteristicas_fisicas),

            'restricciones_medicas' =>
                strip_tags($request->restricciones_medicas),

            'observaciones' =>
                strip_tags($request->observaciones),

            'foto_url' =>
                $request->foto_url,

            'is_active' => true
        ]);

        AuditService::log(

            auth()->id(),

            'CREAR_MASCOTA',

            'mascotas',

            $pet->id,

            null,

            $pet->toArray()
        );

        return response()->json([

            'message' =>
                'Mascota registrada correctamente.',

            'pet' => $pet
        ], 201);
    }

    public function show(
        string $id
    ): JsonResponse {

        $pet = Pet::findOrFail($id);

        $this->authorize('view', $pet);

        return response()->json([

            'pet' => $pet
        ]);
    }

    public function update(
        UpdatePetRequest $request,
        string $id
    ): JsonResponse {

        $pet = Pet::findOrFail($id);

        $this->authorize('update', $pet);

        $datosAntes = $pet->toArray();

        $pet->update([

            'nombre' =>
                strip_tags($request->nombre),

            'especie' =>
                $request->especie,

            'raza' =>
                strip_tags($request->raza),

            'sexo' =>
                $request->sexo,

            'tamanio' =>
                $request->tamanio,

            'edad' =>
                $request->edad,

            'unidad_edad' =>
                $request->unidad_edad,

            'fecha_nacimiento' =>
                $request->fecha_nacimiento,

            'peso' =>
                $request->peso,

            'color' =>
                strip_tags($request->color),

            'caracteristicas_fisicas' =>
                strip_tags($request->caracteristicas_fisicas),

            'restricciones_medicas' =>
                strip_tags($request->restricciones_medicas),

            'observaciones' =>
                strip_tags($request->observaciones),

            'foto_url' =>
                $request->foto_url
        ]);

        AuditService::log(

            auth()->id(),

            'ACTUALIZAR_MASCOTA',

            'mascotas',

            $pet->id,

            $datosAntes,

            $pet->fresh()->toArray()
        );

        return response()->json([

            'message' =>
                'Mascota actualizada correctamente.',

            'pet' => $pet
        ]);
    }

    public function destroy(
        string $id
    ): JsonResponse {

        $pet = Pet::findOrFail($id);

        $this->authorize('delete', $pet);

        $datosAntes = $pet->toArray();

        $pet->delete();

        AuditService::log(

            auth()->id(),

            'ELIMINAR_MASCOTA',

            'mascotas',

            $pet->id,

            $datosAntes,

            null
        );

        return response()->json([

            'message' =>
                'Mascota eliminada correctamente.'
        ]);
    }
}