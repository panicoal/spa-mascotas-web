<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Support\Str;

use App\Models\Service;

use Illuminate\Http\JsonResponse;

use App\Http\Controllers\Controller;

use App\Http\Requests\StoreServiceRequest;

use App\Http\Requests\UpdateServiceRequest;

use App\Services\AuditService;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        $services = Service::withTrashed()
            ->latest()
            ->get();

        return response()->json([
            'services' => $services
        ]);
    }

    public function publicIndex(): JsonResponse
    {
        $services = Service::where('activo', true)->latest()->get();

        return response()->json([
            'services' => $services
        ]);
    }

    public function store(
        StoreServiceRequest $request
    ): JsonResponse {

        $service = Service::create([

            'nombre' =>
                strip_tags($request->nombre),

            'slug' =>
                Str::slug($request->nombre),

            'descripcion' =>
                strip_tags($request->descripcion),

            'precio' =>
                $request->precio,

            'duracion_minutos' =>
                $request->duracion_minutos,

            'categoria' =>
                $request->categoria,

            'imagen_url' =>
                $request->imagen_url,

            'is_active' =>
                $request->boolean('is_active', true)
        ]);

        AuditService::log(
            auth()->id(),
            'CREAR_SERVICIO',
            'services',
            $service->id,
            null,
            $service->toArray()
        );

        return response()->json([

            'message' =>
                'Servicio creado correctamente.',

            'service' => $service
        ], 201);
    }

    public function show(
        string $id
    ): JsonResponse {

        $service = Service::withTrashed()
            ->findOrFail($id);

        return response()->json([
            'service' => $service
        ]);
    }

    public function update(
        UpdateServiceRequest $request,
        string $id
    ): JsonResponse {

        $service = Service::withTrashed()
            ->findOrFail($id);

        $datosAntes = $service->toArray();

        $service->update([

            'nombre' =>
                strip_tags($request->nombre),

            'slug' =>
                Str::slug($request->nombre),

            'descripcion' =>
                strip_tags($request->descripcion),

            'precio' =>
                $request->precio,

            'duracion_minutos' =>
                $request->duracion_minutos,

            'categoria' =>
                $request->categoria,

            'imagen_url' =>
                $request->imagen_url,

            'is_active' =>
                $request->boolean('is_active')
        ]);

        AuditService::log(
            auth()->id(),
            'ACTUALIZAR_SERVICIO',
            'services',
            $service->id,
            $datosAntes,
            $service->fresh()->toArray()
        );

        return response()->json([

            'message' =>
                'Servicio actualizado correctamente.',

            'service' => $service
        ]);
    }

    public function destroy(
        string $id
    ): JsonResponse {

        $service = Service::findOrFail($id);

        $datosAntes = $service->toArray();

        $service->update([
            'is_active' => false
        ]);

        $service->delete();

        AuditService::log(
            auth()->id(),
            'ELIMINAR_SERVICIO',
            'services',
            $service->id,
            $datosAntes,
            null
        );

        return response()->json([
            'message' =>
                'Servicio eliminado correctamente.'
        ]);
    }

    public function restore(
        string $id
    ): JsonResponse {

        $service = Service::withTrashed()
            ->findOrFail($id);

        $service->restore();

        $service->update([
            'is_active' => true
        ]);

        AuditService::log(
            auth()->id(),
            'RESTAURAR_SERVICIO',
            'services',
            $service->id,
            null,
            $service->toArray()
        );

        return response()->json([

            'message' =>
                'Servicio restaurado correctamente.',

            'service' => $service
        ]);
    }
}