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