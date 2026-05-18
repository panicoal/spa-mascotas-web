<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DB::table('auditoria_log')
            ->leftJoin('usuarios', 'auditoria_log.usuario_id', '=', 'usuarios.id')
            ->select(
                'auditoria_log.*',
                'usuarios.nombre_completo',
                'usuarios.email'
            )
            ->orderBy('auditoria_log.created_at', 'desc');

        // Filtros opcionales
        if ($request->has('usuario_id') && $request->usuario_id) {
            $query->where('auditoria_log.usuario_id', $request->usuario_id);
        }

        if ($request->has('accion') && $request->accion) {
            $query->where('auditoria_log.accion', $request->accion);
        }

        if ($request->has('fecha_desde') && $request->fecha_desde) {
            $query->whereDate('auditoria_log.created_at', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta') && $request->fecha_hasta) {
            $query->whereDate('auditoria_log.created_at', '<=', $request->fecha_hasta);
        }

        $logs = $query->paginate(20);

        return response()->json([
            'logs' => $logs
        ]);
    }

    public function show($id): JsonResponse
    {
        $log = DB::table('auditoria_log')
            ->leftJoin('usuarios', 'auditoria_log.usuario_id', '=', 'usuarios.id')
            ->select(
                'auditoria_log.*',
                'usuarios.nombre_completo',
                'usuarios.email'
            )
            ->where('auditoria_log.id', $id)
            ->first();

        if (!$log) {
            return response()->json([
                'message' => 'Log no encontrado'
            ], 404);
        }

        return response()->json([
            'log' => $log
        ]);
    }
}