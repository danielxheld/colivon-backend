<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AwardController;
use App\Http\Controllers\Api\ChoreAssignmentController;
use App\Http\Controllers\Api\ChoreController;
use App\Http\Controllers\Api\FavoriteItemController;
use App\Http\Controllers\Api\GamificationController;
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

    // Shopping Mode routes
    Route::post('/shopping-lists/{shoppingList}/start-shopping', [ShoppingListController::class, 'startShopping']);
    Route::post('/shopping-lists/{shoppingList}/stop-shopping', [ShoppingListController::class, 'stopShopping']);

    // WG Features - Item Claiming
    Route::post('/shopping-list-items/{item}/claim', [ShoppingListController::class, 'claimItem']);
    Route::post('/shopping-list-items/{item}/unclaim', [ShoppingListController::class, 'unclaimItem']);
    Route::post('/shopping-list-items/{item}/mark-bought', [ShoppingListController::class, 'markAsBought']);

    // WG Features - Expenses
    Route::get('/shopping-lists/{shoppingList}/expenses', [ShoppingListController::class, 'getExpenses']);

    // Favorite Items routes
    Route::get('/favorite-items', [FavoriteItemController::class, 'index']);
    Route::post('/favorite-items', [FavoriteItemController::class, 'store']);
    Route::delete('/favorite-items/{favorite}', [FavoriteItemController::class, 'destroy']);

    // Chore routes
    Route::get('/chores', [ChoreController::class, 'index']);
    Route::post('/chores', [ChoreController::class, 'store']);
    Route::get('/chores/{chore}', [ChoreController::class, 'show']);
    Route::put('/chores/{chore}', [ChoreController::class, 'update']);
    Route::delete('/chores/{chore}', [ChoreController::class, 'destroy']);

    // Chore Assignment routes
    Route::get('/chore-assignments', [ChoreAssignmentController::class, 'index']);
    Route::get('/chore-assignments/my', [ChoreAssignmentController::class, 'myAssignments']);
    Route::post('/chores/{chore}/assign', [ChoreAssignmentController::class, 'assign']);
    Route::post('/chore-assignments/{assignment}/complete', [ChoreAssignmentController::class, 'complete']);
    Route::post('/chore-assignments/roulette', [ChoreAssignmentController::class, 'runRoulette']);
    Route::post('/chore-preferences', [ChoreAssignmentController::class, 'updatePreference']);

    // Gamification routes
    Route::get('/gamification/my-stats', [GamificationController::class, 'myStats']);
    Route::get('/gamification/leaderboard/monthly', [GamificationController::class, 'monthlyLeaderboard']);
    Route::get('/gamification/leaderboard/all-time', [GamificationController::class, 'allTimeLeaderboard']);

    // Award routes
    Route::get('/awards', [AwardController::class, 'index']);
    Route::get('/awards/my', [AwardController::class, 'userAwards']);
    Route::post('/awards/check', [AwardController::class, 'checkAwards']);
});
