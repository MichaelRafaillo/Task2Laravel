<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TimesheetController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);

    // User routes
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users/update', [UserController::class, 'update']);
    Route::post('/users/delete', [UserController::class, 'delete']);

    // Project routes
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::post('/projects/update', [ProjectController::class, 'update']);
    Route::post('/projects/delete', [ProjectController::class, 'delete']);

    // Timesheet routes
    Route::post('/timesheets', [TimesheetController::class, 'store']);
    Route::get('/timesheets', [TimesheetController::class, 'index']);
    Route::get('/timesheets/{id}', [TimesheetController::class, 'show']);
    Route::post('/timesheets/update', [TimesheetController::class, 'update']);
    Route::post('/timesheets/delete', [TimesheetController::class, 'delete']);
});

