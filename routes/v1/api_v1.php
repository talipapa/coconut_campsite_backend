<?php

use App\Http\Controllers\Api\v1\BookingController;
use App\Http\Controllers\Api\v1\ManagerController;
use App\Http\Controllers\Api\v1\Mobile\WalletController;
use App\Http\Controllers\Api\v1\TransactionController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\PriceController;
use Illuminate\Support\Facades\Route;


// Route::apiResource('/manager', ManagerController::class)->except(['update', 'destroy']);
// Route::apiResource('/campsite/settings/picture', CampsitePictureController::class)->except(['store', 'destroy']);
Route::apiResource('/price', PriceController::class)->only(['index', 'show']);



Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('/user', UserController::class)->except(['store', 'update', 'destroy']);

    Route::patch('/user/{user}', [UserController::class, 'update']);
    Route::patch('/user/change-password/{user}', [UserController::class, 'changePassword']);

    Route::post('/booking/cancel/{booking}', [BookingController::class, 'cancelBooking']);
    Route::post('/booking/refund/{booking}', [BookingController::class, 'refundBooking']);
    Route::get('/booking/refund/{xendit_refund_id}', [BookingController::class, 'checkRefundStatus']);
    Route::patch('/booking/reschedule/{booking}', [BookingController::class, 'rescheduleBooking']);
    Route::get('/booking/confirmed/{booking}', [BookingController::class, 'confirmedBooking']);
    Route::apiResource('/booking', BookingController::class)->except(['bookingListAll', 'viewOnyCampsiteBooking', 'destroy']);
    Route::get('/booking-check', [BookingController::class, 'showSelfBooking']);
    Route::get('/xendit/{xendit_id}', [TransactionController::class, 'findXenditTransaction']);
    Route::apiResource('/transaction', TransactionController::class)->except(['transactionListAll', 'viewOnlyCampsiteTransaction', 'update', 'destroy']);
});

