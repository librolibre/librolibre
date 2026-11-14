<?php

use App\Http\Controllers\Api\Auth\GoogleAuthController;
use App\Http\Controllers\Api\Profile\MeController;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Laravel's default route returned the raw model (status, strikes...); replaced by GET /me
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('/health', fn () => ['status' => 'ok']);

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

Route::post('/auth/token', [GoogleAuthController::class, 'token']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [MeController::class, 'show']);
});
