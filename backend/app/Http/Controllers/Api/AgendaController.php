<?php

namespace App\Http\Controllers\Api;

use App\Models\Cita;
use App\Models\Pet;
use App\Models\Service;
use App\Models\HorarioGroomer;
use App\Models\BloqueoAgenda;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AgendaController extends Controller
{
    /**
     * Get available slots for scheduling an appointment
     */
    public function getDisponibilidad(Request $request): JsonResponse
    {
        $request->validate([
            'fecha' => 'required|date|after_or_equal:today',
            'servicio_id' => 'required|uuid|exists:servicios,id',
            'mascota_id' => 'required|uuid|exists:mascotas,id',
        ]);

        $fecha = Carbon::parse($request->fecha);
        $diaSemana = $fecha->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.

        $pet = Pet::findOrFail($request->mascota_id);
        $service = Service::findOrFail($request->servicio_id);

        // 1. Calculate adjusted duration based on pet size
        $duracionAjustada = $service->getAdjustedDurationForSize($pet->tamanio ?? 'PEQUEÑO');

        // 2. Fetch all active groomers (only those who exist in groomers table)
        $groomers = User::role('GROOMER')
            ->where('is_active', true)
            ->join('groomers', 'usuarios.id', '=', 'groomers.groomer_id')
            ->select('usuarios.*')
            ->get();

        $disponibilidad = [];

        foreach ($groomers as $groomer) {
            // Check shift working hours
            $horario = HorarioGroomer::where('groomer_id', $groomer->id)
                ->where('dia_semana', $diaSemana)
                ->first();

            // Default shift if none configured (Monday to Friday, 9:00 to 18:00, lunch 13:00-14:00)
            $horaInicio = '09:00:00';
            $horaFin = '18:00:00';
            $pausaInicio = '13:00:00';
            $pausaFin = '14:00:00';
            $trabajaEsteDia = true;

            if ($horario) {
                $horaInicio = $horario->hora_inicio;
                $horaFin = $horario->hora_fin;
                $pausaInicio = $horario->pausa_inicio;
                $pausaFin = $horario->pausa_fin;
            } else {
                // If weekend and no custom shift, they don't work by default
                if ($diaSemana === 0 || $diaSemana === 6) {
                    $trabajaEsteDia = false;
                }
            }

            if (!$trabajaEsteDia) {
                continue;
            }

            // Check if groomer is blocked all day
            $bloqueoTodoElDia = BloqueoAgenda::where('groomer_id', $groomer->id)
                ->where('fecha', $fecha->toDateString())
                ->where('todo_el_dia', true)
                ->exists();

            if ($bloqueoTodoElDia) {
                continue;
            }

            // Get hourly blocks for the day
            $bloqueos = BloqueoAgenda::where('groomer_id', $groomer->id)
                ->where('fecha', $fecha->toDateString())
                ->where('todo_el_dia', false)
                ->get();

            // Get appointments for the day
            $citas = Cita::where('groomer_id', $groomer->id)
                ->where('fecha', $fecha->toDateString())
                ->whereIn('estado', ['PROGRAMADO', 'PENDIENTE_CONFIRMACION', 'EN_PROCESO'])
                ->get();

            // 3. Generate slots (every 30 minutes)
            $start = Carbon::parse($fecha->toDateString() . ' ' . $horaInicio);
            $end = Carbon::parse($fecha->toDateString() . ' ' . $horaFin);
            
            $slotsGroomer = [];

            while ($start->copy()->addMinutes($duracionAjustada)->lte($end)) {
                $slotStart = $start->copy();
                $slotEnd = $start->copy()->addMinutes($duracionAjustada);

                $esValido = true;

                // A. Check if overlaps with lunch break
                if ($pausaInicio && $pausaFin) {
                    $lunchStart = Carbon::parse($fecha->toDateString() . ' ' . $pausaInicio);
                    $lunchEnd = Carbon::parse($fecha->toDateString() . ' ' . $pausaFin);

                    if ($slotStart->lt($lunchEnd) && $slotEnd->gt($lunchStart)) {
                        $esValido = false;
                    }
                }

                // B. Check if overlaps with schedule blocks
                if ($esValido) {
                    foreach ($bloqueos as $bloqueo) {
                        $blockStart = Carbon::parse($fecha->toDateString() . ' ' . $bloqueo->hora_inicio);
                        $blockEnd = Carbon::parse($fecha->toDateString() . ' ' . $bloqueo->hora_fin);

                        if ($slotStart->lt($blockEnd) && $slotEnd->gt($blockStart)) {
                            $esValido = false;
                            break;
                        }
                    }
                }

                // C. Check if overlaps with existing appointments
                if ($esValido) {
                    foreach ($citas as $cita) {
                        $appStart = Carbon::parse($fecha->toDateString() . ' ' . $cita->hora_inicio);
                        $appEnd = Carbon::parse($fecha->toDateString() . ' ' . $cita->hora_fin);

                        if ($slotStart->lt($appEnd) && $slotEnd->gt($appStart)) {
                            $esValido = false;
                            break;
                        }
                    }
                }

                // D. Check if slot start time is in the past (if the date is today)
                if ($esValido && $fecha->isToday()) {
                    if ($slotStart->lt(Carbon::now())) {
                        $esValido = false;
                    }
                }

                if ($esValido) {
                    $slotsGroomer[] = [
                        'hora_inicio' => $slotStart->toTimeString('minute'),
                        'hora_fin' => $slotEnd->toTimeString('minute'),
                        'label' => $slotStart->format('H:i') . ' - ' . $slotEnd->format('H:i')
                    ];
                }

                // Increment by 15 minutes for the next slot to support exact service durations
                $start->addMinutes(15);
            }

            if (!empty($slotsGroomer)) {
                $disponibilidad[] = [
                    'groomer_id' => $groomer->id,
                    'nombre_completo' => $groomer->nombre_completo,
                    'avatar_url' => $groomer->avatar_url,
                    'slots' => $slotsGroomer
                ];
            }
        }

        return response()->json([
            'duracion_minutos' => $duracionAjustada,
            'fecha' => $fecha->toDateString(),
            'disponibilidad' => $disponibilidad
        ]);
    }

    /**
     * Create a blocking in the calendar
     */
    public function storeBloqueo(Request $request): JsonResponse
    {
        $request->validate([
            'groomer_id' => 'required|uuid|exists:usuarios,id',
            'fecha' => 'required|date|after_or_equal:today',
            'todo_el_dia' => 'required|boolean',
            'hora_inicio' => 'required_if:todo_el_dia,false|nullable|date_format:H:i',
            'hora_fin' => 'required_if:todo_el_dia,false|nullable|date_format:H:i|after:hora_inicio',
            'motivo' => 'nullable|string|max:255'
        ]);

        $bloqueo = BloqueoAgenda::create([
            'groomer_id' => $request->groomer_id,
            'fecha' => $request->fecha,
            'todo_el_dia' => $request->todo_el_dia,
            'hora_inicio' => $request->todo_el_dia ? null : $request->hora_inicio,
            'hora_fin' => $request->todo_el_dia ? null : $request->hora_fin,
            'motivo' => $request->motivo,
            'created_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Horario bloqueado correctamente.',
            'bloqueo' => $bloqueo
        ], 201);
    }
}
