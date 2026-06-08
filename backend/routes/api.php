<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\GoogleAuthController;
use App\Http\Controllers\Api\Auth\TwoFactorController;
use App\Http\Controllers\Api\Admin\UserManagementController;
use App\Http\Controllers\Api\Admin\EmployeeController;
use App\Http\Controllers\Api\Admin\AuditLogController;
use App\Http\Controllers\Api\Admin\InventarioController;
use App\Http\Controllers\Api\PetController;
use App\Http\Controllers\Api\Admin\ServiceController;
use App\Http\Controllers\Api\AgendaController;
use App\Http\Controllers\Api\CitaController;
use App\Http\Controllers\Api\GroomingController;
use App\Http\Controllers\Api\ReporteController;

Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/me', function (Request $request) {

            $user = $request->user();
            return response()->json([

                'user' => $user,

                'roles' => $user
                    ->getRoleNames(),

                'permissions' => $user
                    ->getPermissionNames()
            ]);
        });
    });
});

Route::prefix('email')->group(function () {

    Route::get(
        '/verify',
        [EmailVerificationController::class, 'verify']
    );

    Route::middleware('auth:sanctum')->post(
        '/resend',
        [EmailVerificationController::class, 'resend']
    );
});

Route::prefix('auth/google')->group(function () {

    Route::get(
        '/redirect',
        [GoogleAuthController::class, 'redirect']
    );

    Route::get(
        '/callback',
        [GoogleAuthController::class, 'callback']
    );
});

Route::middleware('auth:sanctum')
    ->prefix('2fa')
    ->group(function () {

        Route::post(
            '/generate',
            [TwoFactorController::class, 'generate']
        );

        Route::post(
            '/enable',
            [TwoFactorController::class, 'enable']
        );

        Route::post(
            '/disable',
            [TwoFactorController::class, 'disable']
        );
    });

Route::post(
    '/2fa/verify',
    [TwoFactorController::class, 'verify']
);

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post(
        '/users/change-password',
        [UserManagementController::class, 'changePassword']
    );
});

Route::middleware([
    'auth:sanctum',
    'session.timeout',
    'require.password.change'
]);

Route::middleware([
    'auth:sanctum',
    'require.password.change',
    'role:ADMIN'
])->prefix('admin/users')
->group(function () {

    Route::post(
        '/create-staff',
        [UserManagementController::class,
        'createStaff']
    );
});

Route::middleware(['auth:sanctum', 'require.password.change', 'role:ADMIN'])
    ->prefix('admin')
    ->group(function () {

        Route::get('/employees', [EmployeeController::class, 'index']);

        Route::post('/employees', [EmployeeController::class, 'store']);

        Route::put('/employees/{id}', [EmployeeController::class, 'update']);

        Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);

        Route::patch('/employees/{id}/restore', [EmployeeController::class, 'restore']);

        // Audit logs
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
        Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);
    });

Route::middleware('auth:sanctum')
    ->prefix('pets')
    ->group(function () {

        Route::get('/', [PetController::class, 'index']);

        Route::post('/', [PetController::class, 'store']);

        Route::get('/{id}', [PetController::class, 'show']);

        Route::put('/{id}', [PetController::class, 'update']);

        Route::delete('/{id}', [PetController::class, 'destroy']);
    });

Route::middleware([
    'auth:sanctum',
    'require.password.change',
    'role:ADMIN'
])->prefix('admin')
->group(function () {

    Route::get(
        '/services',
        [ServiceController::class, 'index']
    );

    Route::post(
        '/services',
        [ServiceController::class, 'store']
    );

    Route::get(
        '/services/{id}',
        [ServiceController::class, 'show']
    );

    Route::put(
        '/services/{id}',
        [ServiceController::class, 'update']
    );

    Route::delete(
        '/services/{id}',
        [ServiceController::class, 'destroy']
    );

    Route::patch(
        '/services/{id}/restore',
        [ServiceController::class, 'restore']
    );
});

Route::middleware(['auth:sanctum', 'require.password.change'])->group(function () {
    // Services for all users
    Route::get('/servicios', [ServiceController::class, 'publicIndex']);

    // Agenda and availability slots
    Route::get('/agenda/disponibilidad', [AgendaController::class, 'getDisponibilidad']);
    Route::post('/admin/bloqueos', [AgendaController::class, 'storeBloqueo'])
        ->middleware('role:ADMIN|RECEPCION');

    // Citas operations
    Route::get('/citas', [CitaController::class, 'index']);
    Route::post('/citas', [CitaController::class, 'store']);
    Route::post('/citas/{id}/cancelar', [CitaController::class, 'cancelar']);
    
    // Citas admin/recepcion operations
    Route::middleware('role:ADMIN|RECEPCION')->group(function () {
        Route::put('/citas/{id}/confirmar', [CitaController::class, 'confirmar']);
        Route::put('/citas/{id}/reprogramar', [CitaController::class, 'reprogramar']);
        Route::post('/citas/{id}/pagar', [CitaController::class, 'registrarPago']);
    });

    // Groomer workflow
    Route::middleware('role:ADMIN|GROOMER')->group(function () {
        Route::get('/groomer/agenda', [GroomingController::class, 'agenda']);
        Route::post('/groomer/fichas/{citaId}/iniciar', [GroomingController::class, 'iniciarFicha']);
        Route::post('/groomer/fichas/{citaId}/cerrar', [GroomingController::class, 'cerrarFicha']);
    });

    // ── Reportes y consolidación ────────────────────────────────────────
    Route::middleware(['role:ADMIN'])->group(function () {
        Route::get('/admin/dashboard-kpis', [ReporteController::class, 'dashboardKpis']);
        Route::get('/admin/reporte/mensual-pdf', [ReporteController::class, 'reporteMensualPdf']);
    });

    Route::middleware(['role:ADMIN|RECEPCION'])->group(function () {
        Route::get('/recepcion/cierre-caja', [ReporteController::class, 'cierreCaja']);
        Route::post('/recepcion/cierre-caja', [ReporteController::class, 'cerrarCaja']);
        Route::get('/recepcion/cierre-caja-pdf', [ReporteController::class, 'reporteCierreCajaPdf']);
    });

    // ── Inventario (Admin + Recepcion + Cliente) ──────────────────────────
    Route::get('/inventario/productos', [InventarioController::class, 'index'])
        ->middleware('role:ADMIN|RECEPCION|CLIENTE');

    Route::post('/inventario/pedidos', [InventarioController::class, 'registrarPedidoCliente'])
        ->middleware('role:ADMIN|RECEPCION|CLIENTE');

    Route::middleware('role:ADMIN|RECEPCION')->prefix('inventario')->group(function () {
        Route::get('/dashboard',          [InventarioController::class, 'dashboard']);
        Route::get('/reporte-pdf',        [InventarioController::class, 'reporteInventarioPdf']);
        Route::post('/productos',         [InventarioController::class, 'store']);
        Route::get('/productos/{id}',     [InventarioController::class, 'show']);
        Route::put('/productos/{id}',     [InventarioController::class, 'update']);
        Route::delete('/productos/{id}',  [InventarioController::class, 'destroy']);
        Route::get('/productos/{id}/movimientos',  [InventarioController::class, 'movimientos']);
        Route::post('/productos/{id}/movimiento',  [InventarioController::class, 'registrarMovimiento']);
    });
});