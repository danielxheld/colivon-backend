<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HouseholdController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Household routes
    Route::get('/households', [HouseholdController::class, 'index']);
    Route::post('/households', [HouseholdController::class, 'store']);
    Route::get('/households/{household}', [HouseholdController::class, 'show']);
    Route::put('/households/{household}', [HouseholdController::class, 'update']);
    Route::delete('/households/{household}', [HouseholdController::class, 'destroy']);
    Route::post('/households/join', [HouseholdController::class, 'join']);
    Route::post('/households/{household}/leave', [HouseholdController::class, 'leave']);
});
