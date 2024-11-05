<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


// For normal uses
Route::prefix('v1')->group(base_path('routes/v1/api_v1.php'));

// For mobile
Route::prefix('v1/mobile')->group(base_path('routes/v1/Mobile/mobile_api_v1.php'));

// For manager essentials
Route::prefix('v1/manager')->group(base_path('routes/v1/Desktop/desktop_api_v1.php'));

// Kiosk essentials
Route::prefix('v1/kiosk')->group(base_path('routes/v1/Kiosk/kiosk_api_v1.php'));