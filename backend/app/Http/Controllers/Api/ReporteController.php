<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Pago;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    public function dashboardKpis(Request $request): JsonResponse
    {
        try {
            $now = Carbon::now(config('app.timezone'));
            $today = $now->toDateString();
            $currentMonth = $now->month;
            $currentYear = $now->year;

            $ventasTotales = Pago::whereYear('fecha_pago', $currentYear)
                ->whereMonth('fecha_pago', $currentMonth)
                ->sum('monto_total');

            $productosAlerta = Producto::whereRaw('stock_actual <= stock_minimo')
                ->orderBy('stock_actual')
                ->get([
                    'id',
                    'nombre',
                    'unidad_medida',
                    'stock_actual',
                    'stock_minimo',
                    'categoria',
                    'precio_venta'
                ]);

            $lowStockCount = $productosAlerta->count();

            $activeAppointments = Cita::whereDate('fecha', $today)
                ->where('estado', '!=', 'CANCELADO')
                ->count();

            $dayOfWeek = $now->dayOfWeek;
            $groomersToday = DB::table('horario_trabajo_groomer')
                ->where('dia_semana', $dayOfWeek)
                ->pluck('groomer_id')
                ->unique()
                ->toArray();

            $dailyCapacity = DB::table('empleados')
                ->when(count($groomersToday) > 0, function ($query) use ($groomersToday) {
                    return $query->whereIn('usuario_id', $groomersToday);
                })
                ->sum('max_servicios_simultaneos');

            if ($dailyCapacity === 0) {
                $dailyCapacity = DB::table('empleados')->sum('max_servicios_simultaneos');
            }

            $porcentajeOcupacion = $dailyCapacity > 0
                ? round(($activeAppointments / $dailyCapacity) * 100, 2)
                : 0;

            return response()->json([
                'ventas_totales' => (float) $ventasTotales,
                'porcentaje_ocupacion' => $porcentajeOcupacion,
                'alertas_stock' => $lowStockCount,
                'productos_alerta' => $productosAlerta,
                'capacidad_diaria' => (int) $dailyCapacity,
                'citas_ocupadas' => (int) $activeAppointments,
                'periodo' => [
                    'mes' => $now->locale('es')->monthName,
                    'anio' => $currentYear,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al cargar los indicadores del dashboard.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cierreCaja(Request $request): JsonResponse
    {
        try {
            $today = Carbon::now(config('app.timezone'))->toDateString();
            $supportedMethods = ['EFECTIVO', 'QR', 'TRANSFERENCIA'];

            $payments = Pago::whereDate('fecha_pago', $today)
                ->whereIn('metodo_pago', $supportedMethods)
                ->select('metodo_pago', DB::raw('count(*) as transacciones'), DB::raw('coalesce(sum(monto_total),0) as total_monto'))
                ->groupBy('metodo_pago')
                ->get();

            $summary = $payments->mapWithKeys(function ($item) {
                return [$item->metodo_pago => [
                    'transacciones' => (int) $item->transacciones,
                    'total_monto' => (float) $item->total_monto,
                ]];
            });

            $totalNeto = $summary->sum('total_monto');
            $totalTransacciones = $summary->sum('transacciones');

            return response()->json([
                'fecha' => $today,
                'total_neto' => (float) $totalNeto,
                'total_transacciones' => (int) $totalTransacciones,
                'pagos_por_tipo' => $summary,
                'metodos_soportados' => $supportedMethods,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al obtener el cierre de caja.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cerrarCaja(Request $request): JsonResponse
    {
        try {
            $today = Carbon::now()->toDateString();
            $supportedMethods = ['EFECTIVO', 'QR', 'TRANSFERENCIA'];

            $payments = Pago::whereDate('fecha_pago', $today)
                ->whereIn('metodo_pago', $supportedMethods)
                ->select('metodo_pago', DB::raw('count(*) as transacciones'), DB::raw('coalesce(sum(monto_total),0) as total_monto'))
                ->groupBy('metodo_pago')
                ->get();

            $summary = $payments->mapWithKeys(function ($item) {
                return [$item->metodo_pago => [
                    'transacciones' => (int) $item->transacciones,
                    'total_monto' => (float) $item->total_monto,
                ]];
            });

            $totalNeto = $summary->sum('total_monto');
            $totalTransacciones = $summary->sum('transacciones');

            DB::table('auditoria_log')->insert([
                'usuario_id' => Auth::id(),
                'accion' => 'CIERRE_CAJA',
                'tabla_afectada' => 'pagos',
                'registro_id' => null,
                'datos_antes' => json_encode([
                    'fecha' => $today,
                    'total_neto' => (float) $totalNeto,
                    'total_transacciones' => (int) $totalTransacciones,
                    'pagos_por_tipo' => $summary,
                ]),
                'datos_despues' => json_encode([
                    'estado' => 'CERRADO',
                    'usuario' => Auth::id(),
                    'fecha_cierre' => Carbon::now(config('app.timezone'))->toDateTimeString(),
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => Carbon::now(),
            ]);

            return response()->json([
                'message' => 'Cierre de caja registrado correctamente.',
                'cierre' => [
                    'fecha' => $today,
                    'total_neto' => (float) $totalNeto,
                    'total_transacciones' => (int) $totalTransacciones,
                    'pagos_por_tipo' => $summary,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error al ejecutar el cierre de caja.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate monthly sales report as PDF
     */
    public function reporteMensualPdf(Request $request): Response
    {
        try {
            $now = Carbon::now(config('app.timezone'));
            $month = $request->input('mes', $now->month);
            $year = $request->input('anio', $now->year);

            $ventasTotales = Pago::whereYear('fecha_pago', $year)
                ->whereMonth('fecha_pago', $month)
                ->sum('monto_total');

            $transacciones = Pago::whereYear('fecha_pago', $year)
                ->whereMonth('fecha_pago', $month)
                ->select('metodo_pago', DB::raw('count(*) as cantidad'), DB::raw('sum(monto_total) as total'))
                ->groupBy('metodo_pago')
                ->get();

            $citasCompletadas = Cita::whereYear('fecha', $year)
                ->whereMonth('fecha', $month)
                ->where('estado', 'PAGADO')
                ->count();

            $topProductos = Producto::whereRaw('stock_actual <= stock_minimo')
                ->orderBy('stock_actual')
                ->limit(10)
                ->get();

            $data = [
                'titulo' => 'Reporte Mensual de Ventas',
                'mes' => Carbon::createFromDate($year, $month, 1)->locale('es')->monthName,
                'anio' => $year,
                'ventas_totales' => $ventasTotales,
                'transacciones' => $transacciones,
                'citas_completadas' => $citasCompletadas,
                'productos_bajo_stock' => $topProductos,
                'fecha_generacion' => Carbon::now(config('app.timezone'))->format('d/m/Y H:i'),
                'usuario' => Auth::user()->name,
            ];

            $pdf = Pdf::loadView('reportes.mensual', $data)
                ->setOption('defaultFont', 'Arial')
                ->setOption('dpi', 96)
                ->setOption('enable_remote', true);

            return $pdf->download("Reporte-Mensual-{$year}-{$month}.pdf");
        } catch (\Throwable $e) {
            return response('Error al generar el reporte: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate cash closure report as PDF
     */
    public function reporteCierreCajaPdf(Request $request): Response
    {
        try {
            $today = Carbon::now(config('app.timezone'))->toDateString();
            $supportedMethods = ['EFECTIVO', 'QR', 'TRANSFERENCIA'];

            $payments = Pago::whereDate('fecha_pago', $today)
                ->whereIn('metodo_pago', $supportedMethods)
                ->select('metodo_pago', DB::raw('count(*) as transacciones'), DB::raw('coalesce(sum(monto_total),0) as total_monto'))
                ->groupBy('metodo_pago')
                ->get();

            $summary = $payments->mapWithKeys(function ($item) {
                return [$item->metodo_pago => [
                    'transacciones' => (int) $item->transacciones,
                    'total_monto' => (float) $item->total_monto,
                ]];
            });

            $totalNeto = $summary->sum('total_monto');
            $totalTransacciones = $summary->sum('transacciones');

            $data = [
                'titulo' => 'Cierre de Caja',
                'fecha' => $today,
                'fecha_formateada' => Carbon::parse($today)->locale('es')->isoFormat('dddd D MMMM YYYY'),
                'total_neto' => $totalNeto,
                'total_transacciones' => $totalTransacciones,
                'pagos_por_tipo' => $summary,
                'fecha_generacion' => Carbon::now(config('app.timezone'))->format('d/m/Y H:i'),
                'usuario' => Auth::user()->name,
            ];

            $pdf = Pdf::loadView('reportes.cierre-caja', $data)
                ->setOption('defaultFont', 'Arial')
                ->setOption('dpi', 96);

            return $pdf->download("Cierre-Caja-{$today}.pdf");
        } catch (\Throwable $e) {
            return response('Error al generar el cierre de caja: ' . $e->getMessage(), 500);
        }
    }
}
