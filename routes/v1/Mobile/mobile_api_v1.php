<?php

use App\Http\Controllers\Api\v1\Mobile\BookingController;
use App\Http\Controllers\Api\v1\Mobile\OwnerAccountController;
use App\Http\Controllers\Api\v1\Mobile\WalletController;
use App\Http\Controllers\Api\v1\TokenBasedAuthController;
use Illuminate\Support\Facades\Route;

// Authentication for mobile
Route::post('login', [TokenBasedAuthController::class, 'loginOwner']);
Route::middleware(['auth:sanctum', 'owner'])->group(function (){
    Route::get('user', [TokenBasedAuthController::class, 'user']);   
    Route::post('logout', [TokenBasedAuthController::class, 'logout']);  
    // Booking controller for mobile
    Route::get('bookings/{page}', [BookingController::class, 'showList']);
    Route::get('wallet-summary', [BookingController::class, 'getSummaryWallet']);
    Route::get('dashboard-summary', [BookingController::class, 'dashboardSummary']);
    Route::get('booking/{booking}', [BookingController::class, 'getBookingSummary']);
    Route::patch('/reschedule/{booking}', [BookingController::class, 'rescheduleBooking']);

    // Get booking with successful "VERIFIED status 
    Route::get('/bookings/verified/{page}', [BookingController::class, 'showVerifiedBookings']);
    Route::get('/bookings/current-month/{page}', [BookingController::class, 'showCurrentMonthBookings']);
    Route::get('/bookings/previous-month/{page}', [BookingController::class, 'showPreviousMonthBookings']);
    Route::get('/bookings/cash-only/{page}', [BookingController::class, 'showCashOnlyCurrentMonthBookings']);
    Route::get('/bookings/ewallet-only/{page}', [BookingController::class, 'showEWalletOnlyCurrentMonthBookings']);
    // Get booking with successful "VERIFIED" status
    Route::get('/bookings/verified-only/{page}', [BookingController::class, 'showCurrentMonthVerifiedBookings']);
    Route::get('/bookings/cancelled-only/{page}', [BookingController::class, 'showCurrentMonthCancelledBookings']);
    Route::get('/bookings/scanned/{page}', [BookingController::class, 'showScannedBookings']);

    // If xendit
    Route::post('/refund/{booking}', [BookingController::class, 'refundBooking']);

    // If cash
    Route::post('/cancel/{booking}', [BookingController::class, 'cancelBooking']);

    // Confirmation or no show
    Route::patch('/booking/action/{booking}', [BookingController::class, 'bookingAction']);


    // Show balance
    Route::get('/wallet/balance', [WalletController::class, 'displayWallet']);
    Route::post('/payout', [WalletController::class, 'createPayout']);

    // Owner account routes
    Route::patch('/owner-account/{user}', [OwnerAccountController::class, 'update']);
    Route::patch('/owner-account/change-password/{user}', [OwnerAccountController::class, 'changePassword']);

    // Owner handling manager routes
    Route::get('/managers', [OwnerAccountController::class, 'getManagers']);
    Route::get('/manager/{manager}', [OwnerAccountController::class, 'getSingleManager']);
    Route::post('/manager', [OwnerAccountController::class, 'createManager']);
    Route::patch('/manager/{manager}', [OwnerAccountController::class, 'updateManager']);
    Route::patch('/manager/change-password/{manager}', [OwnerAccountController::class, 'changePasswordManager']);
    Route::delete('/manager/{manager}', [OwnerAccountController::class, 'deleteManager']);

});