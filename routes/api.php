<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    // Public Routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/positions', [AuthController::class, 'getPositions']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/profile', [AuthController::class, 'profile']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        
        // Attendance Routes
        Route::post('/attendance/clock-in', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'checkIn']);
        Route::post('/attendance/clock-out', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'checkOut']);
        Route::get('/attendance/history', [\App\Http\Controllers\Api\V1\AttendanceController::class, 'history']);

        // Incident Reporting
        Route::get('/reports', [\App\Http\Controllers\Api\V1\IncidentReportController::class, 'index']);
        Route::post('/reports', [\App\Http\Controllers\Api\V1\IncidentReportController::class, 'store']);
    });
});
