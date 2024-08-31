<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FoodController;




/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('notification/send-notifications', [NotificationController::class, 'SendNotificationsApi']);
Route::post('notification/schedule-location-check', [NotificationController::class, 'scheduleLocationCheck']);


Route::post('login', [AuthController::class, 'login']);
Route::post('signup', [AuthController::class, 'signup']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('change-role', [AuthController::class, 'changeRole']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/food', [FoodController::class, 'index']); // List all food items
    Route::post('/food', [FoodController::class, 'store']); // Add a new food item
    Route::post('/food/accept/{id}', [FoodController::class, 'accept']); // Accept an order
});
