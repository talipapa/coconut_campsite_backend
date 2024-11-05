<?php

use App\Http\Controllers\Api\v1\Kiosk\KioskController;
use Illuminate\Support\Facades\Route;

// Scan QR code route
Route::middleware(['auth:sanctum', 'manager'])->group(function (){
    Route::post('/scan', [KioskController::class, 'scanQrCode']);
});