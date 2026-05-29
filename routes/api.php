<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/v1/ping', function () {
    return response()->json(['message' => 'pong']);
});

Route::post('/v1/login', [AuthController::class, 'login']);
Route::post('/v1/forgot-password/send-otp', [\App\Http\Controllers\Api\V1\PasswordResetController::class, 'sendOtp']);
Route::post('/v1/forgot-password/reset', [\App\Http\Controllers\Api\V1\PasswordResetController::class, 'resetPassword']);


Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('evaluations', [\App\Http\Controllers\Api\V1\EvaluationController::class, 'index']);
    Route::apiResource('suppliers', \App\Http\Controllers\Api\V1\SupplierController::class)->only(['index', 'show']);
    Route::get('dashboard-stats', [\App\Http\Controllers\Api\V1\DashboardController::class, 'index']);
    Route::apiResource('criteria', \App\Http\Controllers\Api\V1\CriterionController::class)->only(['index']);

    Route::get('decision-histories', [\App\Http\Controllers\Api\V1\DecisionHistoryController::class, 'index']);
    Route::get('decision-histories/{id}', [\App\Http\Controllers\Api\V1\DecisionHistoryController::class, 'show']);

    Route::middleware('role:owner')->group(function () {
        Route::apiResource('users', \App\Http\Controllers\Api\V1\UserController::class);

        // Modul Kriteria
        Route::apiResource('criteria', \App\Http\Controllers\Api\V1\CriterionController::class)->except(['index']);

        // Modul Supplier
        Route::apiResource('suppliers', \App\Http\Controllers\Api\V1\SupplierController::class)->except(['index', 'show']);

        // Modul Matriks Evaluasi
        Route::post('evaluations/bulk', [\App\Http\Controllers\Api\V1\EvaluationController::class, 'bulkStore']);

        // Modul Komputasi EDAS
        Route::post('calculate-edas', [\App\Http\Controllers\Api\V1\EdasCalculationController::class, 'calculate']);
    });

});
