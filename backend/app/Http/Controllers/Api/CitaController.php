<?php

namespace App\Http\Controllers\Api;

use App\Models\Cita;
use App\Models\Pago;
use App\Models\Pet;
use App\Models\Service;
use App\Models\User;
use App\Models\HorarioGroomer;
use App\Models\BloqueoAgenda;
use App\Services\AuditService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CitaController extends Controller
{
    /**
     * Get list of appointments
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        $query = Cita::with(['client', 'pet', 'service', 'groomer', 'groomingCard']);

        // Scope by role
        if ($user->hasRole('CLIENTE')) {
            $query->where('cliente_id', $user->id);
        } elseif ($user->hasRole('GROOMER')) {
            $query->where('groomer_id', $user->id);
        }

        // Filters
        if ($request->filled('fecha')) {
            $query->where('fecha', $request->fecha);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('groomer_id')) {
            $query->where('groomer_id', $request->groomer_id);
        }

        $citas = $query->orderBy('fecha', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->get();

        return response()->json([
            'citas' => $citas
        ]);
    }

    /**
     * Store new appointment request
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();

        $request->validate([
            'mascota_id' => 'required|uuid|exists:mascotas,id',
            'servicio_id' => 'required|uuid|exists:servicios,id',
            'groomer_id' => 'required|uuid|exists:usuarios,id',
            'fecha' => 'required|date|after_or_equal:today',
            'hora_inicio' => 'required|date_format:H:i:s',
            'hora_fin' => 'required|date_format:H:i:s|after:hora_inicio',
        ]);

        // Verify groomer exists in groomers table (not just usuarios)
        $groomerExists = DB::table('groomers')
            ->where('groomer_id', $request->groomer_id)
            ->exists();

        if (!$groomerExists) {
            return response()->json([
                'message' => 'El groomer seleccionado no está disponible para agendar citas.'
            ], 422);
        }

        // Load service and pet to verify duration and ownership
        $pet     = Pet::findOrFail($request->mascota_id);
        $service = Service::findOrFail($request->servicio_id);

        if ($user->hasRole('CLIENTE') && $pet->cliente_id !== $user->id) {
            return response()->json([
                'message' => 'No autorizado para agendar citas para esta mascota.'
            ], 403);
        }

        // ── Duration Validation (Agenda Item 1) ─────────────────────────────
        $expectedMinutes = $service->getAdjustedDurationForSize($pet->tamanio ?? 'MEDIANO');

        $slotStart = Carbon::parse($request->fecha . ' ' . $request->hora_inicio);
        $slotEnd   = Carbon::parse($request->fecha . ' ' . $request->hora_fin);
        $slotMinutes = $slotStart->diffInMinutes($slotEnd);

        if ($slotMinutes < $expectedMinutes) {
            return response()->json([
                'message' => "El slot seleccionado ({$slotMinutes} min) es insuficiente para este servicio."
                           . " Se requieren {$expectedMinutes} minutos para una mascota de tamaño {$pet->tamanio}."
            ], 422);
        }

        // Check if there is already an appointment at this slot
        $overlap = Cita::where('groomer_id', $request->groomer_id)
            ->where('fecha', $request->fecha)
            ->whereIn('estado', ['PROGRAMADO', 'PENDIENTE_CONFIRMACION', 'EN_PROCESO'])
            ->where(function ($query) use ($slotStart, $slotEnd) {
                $query->where(function ($q) use ($slotStart, $slotEnd) {
                    $q->where('hora_inicio', '<', $slotEnd->toTimeString())
                      ->where('hora_fin', '>', $slotStart->toTimeString());
                });
            })
            ->exists();

        if ($overlap) {
            return response()->json([
                'message' => 'El slot seleccionado ya está ocupado por otra cita.'
            ], 422);
        }

        // Check schedule blocking
        $blocked = BloqueoAgenda::where('groomer_id', $request->groomer_id)
            ->where('fecha', $request->fecha)
            ->where(function ($query) use ($slotStart, $slotEnd) {
                $query->where('todo_el_dia', true)
                    ->orWhere(function ($q) use ($slotStart, $slotEnd) {
                        $q->where('hora_inicio', '<', $slotEnd->toTimeString())
                          ->where('hora_fin', '>', $slotStart->toTimeString());
                    });
            })
            ->exists();

        if ($blocked) {
            return response()->json([
                'message' => 'El horario seleccionado está bloqueado para el groomer.'
            ], 422);
        }

        // ── Capacity Validation (Agenda Item 2) ─────────────────────────────
        // Max active appointments per groomer per day = 8 (configurable)
        $maxCitasPorDia = 8;
        $citasDelDia = Cita::where('groomer_id', $request->groomer_id)
            ->where('fecha', $request->fecha)
            ->whereIn('estado', ['PROGRAMADO', 'PENDIENTE_CONFIRMACION', 'EN_PROCESO'])
            ->count();

        if ($citasDelDia >= $maxCitasPorDia) {
            return response()->json([
                'message' => "El groomer ya tiene el máximo de {$maxCitasPorDia} citas para ese día. Agenda bloqueada."
            ], 422);
        }

        // Determine initial status: CLIENTE makes request -> PENDIENTE_CONFIRMACION, RECEPCION/ADMIN makes request -> PROGRAMADO
        $estadoInicial = $user->hasRole('CLIENTE') ? 'PENDIENTE_CONFIRMACION' : 'PROGRAMADO';

        $cita = Cita::create([
            'cliente_id' => $pet->cliente_id,
            'mascota_id' => $request->mascota_id,
            'servicio_id' => $request->servicio_id,
            'groomer_id' => $request->groomer_id,
            'fecha' => $request->fecha,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'estado' => $estadoInicial,
            'creada_por' => $user->id
        ]);

        AuditService::log(
            $user->id,
            'CREAR_CITA',
            'citas',
            $cita->id,
            null,
            $cita->toArray()
        );

        return response()->json([
            'message' => $user->hasRole('CLIENTE') 
                ? 'Solicitud de cita enviada. En espera de confirmación de recepción.' 
                : 'Cita agendada correctamente.',
            'cita' => $cita
        ], 210); // 210 to match client expectations
    }

    /**
     * Confirm/Approve a requested appointment (Reception)
     */
    public function confirmar(string $id): JsonResponse
    {
        $cita = Cita::findOrFail($id);

        if ($cita->estado !== 'PENDIENTE_CONFIRMACION') {
            return response()->json([
                'message' => 'La cita no se encuentra en estado pendiente.'
            ], 422);
        }

        $datosAntes = $cita->toArray();

        $cita->update([
            'estado' => 'PROGRAMADO'
        ]);

        AuditService::log(
            auth()->id(),
            'CONFIRMAR_CITA',
            'citas',
            $cita->id,
            $datosAntes,
            $cita->fresh()->toArray()
        );

        return response()->json([
            'message' => 'Cita confirmada correctamente.',
            'cita' => $cita
        ]);
    }

    /**
     * Reprogram/Move an appointment (Reception Calendar Drag and Drop)
     */
    public function reprogramar(Request $request, string $id): JsonResponse
    {
        $cita = Cita::findOrFail($id);

        $request->validate([
            'fecha' => 'required|date|after_or_equal:today',
            'hora_inicio' => 'required|date_format:H:i:s',
            'hora_fin' => 'required|date_format:H:i:s|after:hora_inicio',
            'groomer_id' => 'required|uuid|exists:usuarios,id'
        ]);

        // Verify groomer exists in groomers table (not just usuarios)
        $groomerExists = DB::table('groomers')
            ->where('groomer_id', $request->groomer_id)
            ->exists();

        if (!$groomerExists) {
            return response()->json([
                'message' => 'El groomer seleccionado no está disponible para agendar citas.'
            ], 422);
        }

        // Validate availability for the new slot (excluding current appointment id)
        $slotStart = Carbon::parse($request->fecha . ' ' . $request->hora_inicio);
        $slotEnd   = Carbon::parse($request->fecha . ' ' . $request->hora_fin);

        // ── Duration Validation (reprogramar) ────────────────────────────────
        $service     = Service::findOrFail($cita->servicio_id);
        $pet         = Pet::findOrFail($cita->mascota_id);
        $expectedMinutes = $service->getAdjustedDurationForSize($pet->tamanio ?? 'MEDIANO');
        $slotMinutes = $slotStart->diffInMinutes($slotEnd);

        if ($slotMinutes < $expectedMinutes) {
            return response()->json([
                'message' => "El nuevo slot ({$slotMinutes} min) es insuficiente. Se requieren {$expectedMinutes} min para este servicio."
            ], 422);
        }

        $overlap = Cita::where('id', '!=', $id)
            ->where('groomer_id', $request->groomer_id)
            ->where('fecha', $request->fecha)
            ->whereIn('estado', ['PROGRAMADO', 'PENDIENTE_CONFIRMACION', 'EN_PROCESO'])
            ->where(function ($query) use ($slotStart, $slotEnd) {
                $query->where(function ($q) use ($slotStart, $slotEnd) {
                    $q->where('hora_inicio', '<', $slotEnd->toTimeString())
                      ->where('hora_fin', '>', $slotStart->toTimeString());
                });
            })
            ->exists();

        if ($overlap) {
            return response()->json([
                'message' => 'El horario seleccionado para reprogramar ya está ocupado.'
            ], 422);
        }


        $datosAntes = $cita->toArray();

        $cita->update([
            'fecha' => $request->fecha,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'groomer_id' => $request->groomer_id
        ]);

        AuditService::log(
            auth()->id(),
            'REPROGRAMAR_CITA',
            'citas',
            $cita->id,
            $datosAntes,
            $cita->fresh()->toArray()
        );

        return response()->json([
            'message' => 'Cita reprogramada correctamente.',
            'cita' => $cita
        ]);
    }

    /**
     * Cancel an appointment
     */
    public function cancelar(Request $request, string $id): JsonResponse
    {
        $user = auth()->user();
        $cita = Cita::findOrFail($id);

        $request->validate([
            'motivo_cancelacion' => 'required|string|min:5|max:500'
        ]);

        // Security check
        if ($user->hasRole('CLIENTE') && $cita->cliente_id !== $user->id) {
            return response()->json([
                'message' => 'No autorizado para cancelar esta cita.'
            ], 403);
        }

        $datosAntes = $cita->toArray();

        $cita->update([
            'estado' => 'CANCELADO',
            'motivo_cancelacion' => strip_tags($request->motivo_cancelacion)
        ]);

        AuditService::log(
            $user->id,
            'CANCELAR_CITA',
            'citas',
            $cita->id,
            $datosAntes,
            $cita->fresh()->toArray()
        );

        return response()->json([
            'message' => 'Cita cancelada correctamente.',
            'cita' => $cita
        ]);
    }

    /**
     * Record payment and checkout service (Reception)
     */
    public function registrarPago(Request $request, string $id): JsonResponse
    {
        $cita = Cita::findOrFail($id);

        $request->validate([
            'metodo_pago'      => 'required|string|in:QR,EFECTIVO,TRANSFERENCIA',
            'monto'            => 'required|numeric|min:0',
            'monto_descuento'  => 'nullable|numeric|min:0',
            'referencia'       => 'nullable|string|max:100',
            'notas'            => 'nullable|string|max:500',
        ]);

        if (!in_array($cita->estado, ['FINALIZADO', 'PROGRAMADO'])) {
            return response()->json([
                'message' => 'No se puede procesar el pago para citas en estado ' . $cita->estado
            ], 422);
        }

        $descuento   = (float) ($request->monto_descuento ?? 0);
        $montoTotal  = max(0, (float) $request->monto - $descuento);
        $datosAntes  = $cita->toArray();

        // 1. Persist payment to pagos table
        $pago = Pago::create([
            'cita_id'         => $cita->id,
            'registrado_por'  => auth()->id(),
            'metodo_pago'     => $request->metodo_pago,
            'monto'           => $request->monto,
            'monto_descuento' => $descuento,
            'monto_total'     => $montoTotal,
            'referencia'      => $request->referencia,
            'notas'           => strip_tags($request->notas ?? ''),
        ]);

        // 2. Update appointment status to PAGADO
        $cita->update([
            'estado' => 'PAGADO'
        ]);

        AuditService::log(
            auth()->id(),
            'REGISTRAR_PAGO_CITA',
            'pagos',
            $pago->id,
            $datosAntes,
            [
                'cita'      => $cita->fresh()->toArray(),
                'pago'      => $pago->toArray(),
            ]
        );

        return response()->json([
            'message' => 'Pago registrado correctamente. Cita cobrada y cerrada.',
            'cita'    => $cita,
            'pago'    => $pago,
        ]);
    }
}
