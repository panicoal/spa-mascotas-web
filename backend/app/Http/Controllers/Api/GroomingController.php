<?php

namespace App\Http\Controllers\Api;

use App\Models\Cita;
use App\Models\FichaGrooming;
use App\Mail\PickupReadyMail;
use App\Services\AuditService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class GroomingController extends Controller
{
    /**
     * Get groomer's agenda (services assigned to them today)
     */
    /*
    public function agenda(Request $request): JsonResponse
    {
        $groomerId = auth()->id();
        $fecha = $request->input('fecha', Carbon::today()->toDateString());

        $citas = Cita::with(['client', 'pet', 'service', 'groomingCard'])
            ->where('groomer_id', $groomerId)
            ->where('fecha', $fecha)
            ->whereIn('estado', ['PROGRAMADO', 'EN_PROCESO', 'FINALIZADO', 'PAGADO'])
            ->orderBy('hora_inicio', 'asc')
            ->get();

        return response()->json([
            'citas' => $citas
        ]);
    }
    */
    public function agenda(Request $request): JsonResponse
{
    $groomerId = auth()->id();

    // dia | semana | mes
    $vista = $request->input('vista', 'dia');

    $query = Cita::with([
            'client',
            'pet',
            'service',
            'groomingCard'
        ])
        ->where('groomer_id', $groomerId)
        ->whereIn('estado', [
            'PROGRAMADO',
            'EN_PROCESO',
            'FINALIZADO',
            'PAGADO'
        ]);

    switch ($vista) {

        case 'mes':

            $query->whereBetween('fecha', [
                Carbon::now()->startOfMonth()->toDateString(),
                Carbon::now()->endOfMonth()->toDateString()
            ]);

            break;

        case 'semana':

            $query->whereBetween('fecha', [
                Carbon::now()->startOfWeek()->toDateString(),
                Carbon::now()->endOfWeek()->toDateString()
            ]);

            break;

        default:

            $query->whereDate(
                'fecha',
                Carbon::today()->toDateString()
            );

            break;
    }

    $citas = $query
        ->orderBy('fecha', 'asc')
        ->orderBy('hora_inicio', 'asc')
        ->get();

    return response()->json([
        'citas' => $citas
    ]);
}

    /**
     * Start grooming service (Open technical card)
     */
    public function iniciarFicha(string $citaId): JsonResponse
    {
        $groomerId = auth()->id();
        $cita = Cita::findOrFail($citaId);

        // Security check
        if ($cita->groomer_id !== $groomerId && !auth()->user()->hasRole('ADMIN')) {
            return response()->json([
                'message' => 'No autorizado para atender esta cita.'
            ], 403);
        }

        if ($cita->estado !== 'PROGRAMADO') {
            return response()->json([
                'message' => 'La cita no está en estado programado para iniciar.'
            ], 422);
        }

        $datosAntes = $cita->toArray();

        // 1. Update appointment state to EN_PROCESO
        $cita->update([
            'estado' => 'EN_PROCESO'
        ]);

        // 2. Open Ficha Grooming
        $ficha = FichaGrooming::updateOrCreate(
            ['cita_id' => $cita->id],
            [
                'groomer_id' => $cita->groomer_id,
                'fecha_apertura' => Carbon::now(),
                'temperamento' => $cita->pet->restricciones_medicas ? 'NERVIOSO' : 'TRANQUILO' // default suggestion
            ]
        );

        AuditService::log(
            $groomerId,
            'INICIAR_GROOMING_FICHA',
            'fichas_grooming',
            $ficha->id,
            $datosAntes,
            [
                'cita' => $cita->fresh()->toArray(),
                'ficha' => $ficha->fresh()->toArray()
            ]
        );

        return response()->json([
            'message' => 'Servicio iniciado. Ficha técnica abierta.',
            'cita' => $cita->load('groomingCard'),
            'ficha' => $ficha
        ]);
    }

    /**
     * Close grooming service (Complete checklist, upload photos, close technical card)
     */
    public function cerrarFicha(Request $request, string $citaId): JsonResponse
    {
        $groomerId = auth()->id();
        $cita = Cita::findOrFail($citaId);

        // Security check
        if ($cita->groomer_id !== $groomerId && !auth()->user()->hasRole('ADMIN')) {
            return response()->json([
                'message' => 'No autorizado para cerrar esta cita.'
            ], 403);
        }

        if ($cita->estado !== 'EN_PROCESO') {
            return response()->json([
                'message' => 'El servicio debe estar en proceso para poder cerrarse.'
            ], 422);
        }

        $rules = [
            'estado_ingreso_nudos' => 'required|string|in:NO,MODERADO,SEVERO',
            'estado_ingreso_pulgas' => 'required|boolean',
            'estado_ingreso_heridas' => 'nullable|string',
            'temperamento' => 'required|string|in:TRANQUILO,NERVIOSO,AGRESIVO,INQUIETO',
            'condicion_general' => 'required|string|min:5|max:1000',
            'recomendaciones_tecnicas' => 'nullable|string|max:1000',
            'tiempo_real_minutos' => 'required|integer|min:5|max:480',
            'checklist' => 'required|array|min:1', // Must complete at least 1 item
            'checklist.*' => 'string',
            'fotos' => 'nullable|array'
        ];

        if ($request->hasFile('fotos')) {
            $rules['fotos.*'] = 'file|image|mimes:jpeg,png,jpg,gif|max:5120';
        } elseif ($request->has('fotos')) {
            $rules['fotos.*'] = 'string';
        }

        $request->validate($rules);

        $uploadedPhotoPaths = [];
        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $photo) {
                if ($photo->isValid()) {
                    $storedPath = $photo->store('grooming_fotos', 'public');
                    $uploadedPhotoPaths[] = Storage::disk('public')->url($storedPath);
                }
            }
        }

        $storedFotos = [];
        if (is_array($request->input('fotos', []))) {
            $storedFotos = $request->input('fotos', []);
        }

        $fotos = array_merge($storedFotos, $uploadedPhotoPaths);

        $ficha = FichaGrooming::where('cita_id', $cita->id)->firstOrFail();
        $fichaAntes = $ficha->toArray();
        $citaAntes = $cita->toArray();

        // 1. Update Ficha Grooming details
        $ficha->update([
            'estado_ingreso_nudos' => $request->estado_ingreso_nudos,
            'estado_ingreso_pulgas' => $request->estado_ingreso_pulgas,
            'estado_ingreso_heridas' => strip_tags($request->estado_ingreso_heridas),
            'temperamento' => $request->temperamento,
            'condicion_general' => strip_tags($request->condicion_general),
            'recomendaciones_tecnicas' => strip_tags($request->recomendaciones_tecnicas),
            'tiempo_real_minutos' => $request->tiempo_real_minutos,
            'checklist' => $request->checklist,
            'fotos' => $fotos,
            'fecha_cierre' => Carbon::now()
        ]);

        // 2. Change Cita status to FINALIZADO (waiting for pick-up/checkout payment)
        $cita->update([
            'estado' => 'FINALIZADO'
        ]);

        AuditService::log(
            $groomerId,
            'CERRAR_GROOMING_FICHA',
            'fichas_grooming',
            $ficha->id,
            [
                'cita' => $citaAntes,
                'ficha' => $fichaAntes
            ],
            [
                'cita' => $cita->fresh()->toArray(),
                'ficha' => $ficha->fresh()->toArray()
            ]
        );

        // 3. Send pickup notification email to client
        $emailEnviado = false;
        try {
            if ($cita->client?->email) {
                Mail::to($cita->client->email)
                    ->send(new PickupReadyMail($cita->load(['pet', 'service', 'groomer', 'client'])));
                $emailEnviado = true;
            }
        } catch (\Exception $e) {
            // Log failure but don't break the response
            \Illuminate\Support\Facades\Log::warning('Fallo al enviar email de pickup: ' . $e->getMessage());
        }

        // 4. Automatic stock reduction of grooming supplies (shampoo and conditioner)
        $insumosDescontados = [];
        try {
            $shampoo = \App\Models\Producto::where('codigo', 'SHAMP-AV-01')->first();
            $acondicionador = \App\Models\Producto::where('codigo', 'ACOND-BS-02')->first();

            if ($shampoo && $shampoo->stock_actual > 0) {
                $stockAnterior = $shampoo->stock_actual;
                $nuevoStock = $stockAnterior - 1;
                $shampoo->update(['stock_actual' => $nuevoStock]);

                \App\Models\MovimientoInventario::create([
                    'producto_id'    => $shampoo->id,
                    'usuario_id'     => $groomerId,
                    'cita_id'        => $cita->id,
                    'tipo_movimiento'           => 'SALIDA',
                    'motivo'         => "Consumo automático en servicio de grooming de {$cita->pet->nombre}",
                    'cantidad'       => 1,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo'    => $nuevoStock,
                ]);

                $insumosDescontados[] = [
                    'nombre' => $shampoo->nombre,
                    'cantidad' => 1,
                    'stock_restante' => $nuevoStock
                ];
            }

            if ($acondicionador && $acondicionador->stock_actual > 0) {
                $stockAnterior = $acondicionador->stock_actual;
                $nuevoStock = $stockAnterior - 1;
                $acondicionador->update(['stock_actual' => $nuevoStock]);

                \App\Models\MovimientoInventario::create([
                    'producto_id'    => $acondicionador->id,
                    'usuario_id'     => $groomerId,
                    'cita_id'        => $cita->id,
                    'tipo_movimiento'           => 'SALIDA',
                    'motivo'         => "Consumo automático en servicio de grooming de {$cita->pet->nombre}",
                    'cantidad'       => 1,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo'    => $nuevoStock,
                ]);

                $insumosDescontados[] = [
                    'nombre' => $acondicionador->nombre,
                    'cantidad' => 1,
                    'stock_restante' => $nuevoStock
                ];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Fallo al descontar insumos de inventario: ' . $e->getMessage());
        }

        // Simulation of customer pick-up notification
        $mensajeNotificacion = "¡Tu mascota {$cita->pet->nombre} está lista para recoger! Te esperamos en Pet Spa.";

        return response()->json([
            'message' => 'Ficha técnica guardada y servicio cerrado con éxito.',
            'cita' => $cita->load('groomingCard'),
            'ficha' => $ficha,
            'insumos_descontados' => $insumosDescontados,
            'notificacion_cliente' => [
                'enviado'  => $emailEnviado,
                'mensaje'  => $mensajeNotificacion,
                'cliente'  => $cita->client->nombre_completo,
                'email'    => $cita->client->email,
                'telefono' => $cita->client->telefono
            ]
        ]);
    }
}
