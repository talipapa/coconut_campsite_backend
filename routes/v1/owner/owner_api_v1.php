<?php

use App\Http\Controllers\Api\v1\BookingController;
use App\Http\Controllers\Api\v1\TransactionController;
use App\Models\Manager;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    

    // Admin should be able to only designate a campsite manager profile
    Route::apiResource('/manager', Manager::class);
    
    // Admin should be the only one that able to update and delete transactions
    Route::apiResource('/transaction', TransactionController::class)->only(['update', 'destroy']);

    // Admin should be the only one that able to delete bookings
    Route::apiResource('/booking', BookingController::class)->only(['destroy']);
});