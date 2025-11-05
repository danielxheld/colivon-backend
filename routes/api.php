<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HouseholdController;
use App\Http\Controllers\Api\ShoppingListController;
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

    // Shopping List routes
    Route::get('/shopping-lists', [ShoppingListController::class, 'index']);
    Route::post('/shopping-lists', [ShoppingListController::class, 'store']);
    Route::get('/shopping-lists/{shoppingList}', [ShoppingListController::class, 'show']);
    Route::put('/shopping-lists/{shoppingList}', [ShoppingListController::class, 'update']);
    Route::delete('/shopping-lists/{shoppingList}', [ShoppingListController::class, 'destroy']);

    // Shopping List Item routes
    Route::post('/shopping-lists/{shoppingList}/items', [ShoppingListController::class, 'addItem']);
    Route::put('/shopping-list-items/{item}', [ShoppingListController::class, 'updateItem']);
    Route::post('/shopping-list-items/{item}/toggle', [ShoppingListController::class, 'toggleItemComplete']);
    Route::delete('/shopping-list-items/{item}', [ShoppingListController::class, 'deleteItem']);
});
