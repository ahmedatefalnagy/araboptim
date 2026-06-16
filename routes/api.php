<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Driver Mobile App APIs
Route::post('/driver/login', [App\Http\Controllers\Api\DriverApiController::class, 'login']);

Route::middleware('auth:sanctum')->prefix('driver')->group(function () {
    Route::get('/profile', [App\Http\Controllers\Api\DriverApiController::class, 'profile']);
    Route::post('/profile/upload-document', [App\Http\Controllers\Api\DriverApiController::class, 'uploadDocument']);
    Route::get('/active-trip', [App\Http\Controllers\Api\DriverApiController::class, 'activeTrip']);
    Route::post('/upload-loading', [App\Http\Controllers\Api\DriverApiController::class, 'uploadLoadingInvoice']);
    Route::post('/upload-delivery', [App\Http\Controllers\Api\DriverApiController::class, 'uploadDeliveryInvoice']);
    Route::post('/update-location', [App\Http\Controllers\Api\DriverApiController::class, 'updateLocation']);
    Route::get('/trips-history', [App\Http\Controllers\Api\DriverApiController::class, 'tripsHistory']);
    Route::get('/admin/drivers', [App\Http\Controllers\Api\DriverApiController::class, 'getDriversForAdmin']);
    Route::get('/admin/cash-register', [App\Http\Controllers\Api\DriverApiController::class, 'getCashRegisterForAdmin']);
    Route::get('/admin/expenses', [App\Http\Controllers\Api\DriverApiController::class, 'getExpensesForAdmin']);
});
