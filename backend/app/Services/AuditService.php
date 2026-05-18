<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AuditService
{
    public static function log(
        $usuarioId,
        $accion,
        $tabla = null,
        $registroId = null,
        $antes = null,
        $despues = null
    ) {

        DB::table('auditoria_log')->insert([

            'usuario_id' => $usuarioId,

            'accion' => $accion,

            'tabla_afectada' => $tabla,

            'registro_id' => $registroId,

            'datos_antes' => $antes
                ? json_encode($antes)
                : null,

            'datos_despues' => $despues
                ? json_encode($despues)
                : null,

            'ip_address' => request()->ip(),

            'user_agent' => request()->userAgent(),

            'created_at' => now()
        ]);
    }
}