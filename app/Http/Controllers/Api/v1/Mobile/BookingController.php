<?php

namespace App\Http\Controllers\Api\v1\Mobile;

use App\CustomVendors\Xendivel as CustomVendorsXendivel;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\SuccessfulBookingResource;
use App\Models\Booking;
use App\Models\Transaction;
use Carbon\Carbon;
use GlennRaya\Xendivel\Xendivel;
use Hamcrest\Type\IsNumeric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\ErrorLogHandler;

class BookingController extends Controller
{
    //
    function showList(Request $request, $page){
        // Get all bookings
        $bookings = [];
        
        // Check if $page is number
        if (is_numeric($page) && $page > 0) {
            $bookings = Booking::whereIn('status', ['SCANNED', 'PAID', 'PENDING', 'CASH_PENDING'])->orderBy('check_in', 'asc')->paginate($page);

            // $bookings = Booking::whereHas('transaction', function ($query) {
            //     $query->whereIn('status', ['CASH_PENDING', 'SUCCEEDED']);
            // })->orderBy('check_in', 'asc')->paginate($page);
        } else {
            $bookings = Booking::whereIn('status', ['SCANNED', 'PAID', 'PENDING', 'CASH_PENDING'])->orderBy('check_in', 'asc');
            // $bookings = Booking::all()->filter(function ($booking) {
            //     return $booking->transaction !== null && in_array($booking->transaction->status, ['CASH_PENDING', 'SUCCEEDED']);
            // })->sortByAsc('check_in');
        }

        // Return all bookings with successful bookingresource
        return response()->json([
            'bookings' => SuccessfulBookingResource::collection($bookings)
        ], 200);
    }

    public function dashboardSummary(Request $request){
        try {
            // total earnings of current year
            $totalYearEarnings = Transaction::whereYear('updated_at', Carbon::now()->year)
            ->whereIn('status', ['VERIFIED'])
            ->sum('price');

            // total earnings this month
            $totalMonthEarnings = Transaction::whereYear('updated_at', Carbon::now()->year)
            ->whereMonth('updated_at', Carbon::now()->month)
            ->where('status', ['VERIFIED', 'SCANNED', 'SUCCEEDED'])
            ->sum('price');

            // total earnings from previous month
            $startOfPreviousMonth = Carbon::now()->startOfMonth()->subMonth();
            $endOfPreviousMonth = Carbon::now()->subMonth()->endOfMonth();

            $totalPreviousMonthEarnings = Transaction::whereBetween('updated_at', [$startOfPreviousMonth, $endOfPreviousMonth])->whereIn('status', ['VERIFIED'])->sum('price');

            // cash revenue this month
            $cashRevenueThisMonth = Transaction::whereYear('updated_at', Carbon::now()->year)
            ->whereMonth('updated_at', Carbon::now()->month)
            ->whereIn('status', ['VERIFIED'])
            ->where('payment_type', 'CASH')
            ->sum('price');

            // e-payment revenue this month
            $ePaymentRevenueThisMonth = Transaction::whereYear('updated_at', Carbon::now()->year)
            ->whereMonth('updated_at', Carbon::now()->month)
            ->whereIn('status', ['VERIFIED'])
            ->where('payment_type', 'XENDIT')
            ->sum('price');

            // success booking this month
            $successBookingThisMonth = Transaction::whereYear('updated_at', Carbon::now()->year)
            ->whereMonth('updated_at', Carbon::now()->month)
            ->whereIn('status', ['VERIFIED'])
            ->count();

            // cancelled booking this month
            $cancelledBookingThisMonth = Transaction::whereYear('updated_at', Carbon::now()->year)
            ->whereMonth('updated_at', Carbon::now()->month)
            ->whereIn('status', ['CANCELLED', 'VOIDED', 'REFUNDED', 'FAILED'])
            ->count();
    

            // Wallet in XENDIT
            $response = CustomVendorsXendivel::getBalance()->getResponse();
            $xenditWallet = json_decode(json_encode($response), true)['balance'];

            return response()->json([
                'totalYearEarnings' => $totalYearEarnings,
                'totalMonthEarnings' => $totalMonthEarnings,
                'totalPreviousMonthEarnings' => $totalPreviousMonthEarnings,
                'cashRevenueThisMonth' => $cashRevenueThisMonth,
                'ePaymentRevenueThisMonth' => $ePaymentRevenueThisMonth,
                'successBookingThisMonth' => $successBookingThisMonth,
                'cancelledBookingThisMonth' => $cancelledBookingThisMonth,
                'xenditWallet' => $xenditWallet,
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something went wrong', 'error' => $th], 500);
        }
    }

    function getSummaryWallet(Request $request){
        $summaryData = [
            'wallet' => 0,
            'successfullTotalBookingCount' => 0,
            'pendingCash' => 0,
            'pendingTotalBookingCount' => 0,
        ];
    

        // Sum all price of transaction with 'VERIFIED' status
        $response = CustomVendorsXendivel::getBalance()->getResponse();
        $summaryData['wallet'] = json_decode(json_encode($response), true)['balance'];

        
        // // Sum all count of transaction with 'SUCCEEDED' status
        // $summaryData['successfullTotalBookingCount'] = Booking::where('status', 'PAID')->get()->count();
        
        // // Sum all price of transaction with 'CASH_PENDING & PENDING' status using where
        // $summaryData['pendingCash'] = Booking::whereIn('status', ['CASH_PENDING', 'PENDING'])->get()->sum(function($booking) {
        //     if ($booking->transaction === null){
        //         return 0;
        //     }
        //     return $booking->transaction->price;
        // });
        

        // // Sum all count of transaction with 'CASH_PENDING & PENDING' status
        // $summaryData['pendingTotalBookingCount'] = Booking::whereIn('status', ['CASH_PENDING', 'PENDING'])->whereHas('transaction')->count();

        return response()->json([
            'summary' => $summaryData
        ], 200);
    }

    function getBookingSummary(Request $request, Booking $booking){
        // IF booking doesnt exist throw 404 response
        if (!$booking) return response()->json(['message' => 'Booking not found'], 404);
    
        switch ($booking->transaction->payment_type) {
            case 'CASH':
                $details = [
                    "booking_detail" => $booking,
                ];

                $createdAt = Carbon::parse($booking->updated_at)->timezone('Asia/Manila');
                $now = Carbon::now()->timezone('Asia/Manila');
                $cutOffTime = $createdAt->copy()->setTime(23, 40, 0);
                
                if ($createdAt->isSameDay($now) && $createdAt->lte($cutOffTime)) {
                    $details['isVoidEligible'] = true;
                } else{
                    $details['isVoidEligible'] = false;
                }
                
                return response()->json($details, 200);
                break;

            case 'XENDIT':
                $details = [
                    "booking_detail" => $booking,
                    "xendit_details" => [],
                    "isVoidEligible" => null,
                ];

                try {
                    $xenditId = 'ewc_'.$booking->transaction->xendit_product_id;
                    
                    $response = Xendivel::getPayment($xenditId, 'ewallet')
                    ->getResponse();
                    $details['xendit_details'] = $response;

                    $createdAt = Carbon::parse($response->created)->timezone('Asia/Manila');
                    $now = Carbon::now()->timezone('Asia/Manila');
                    $cutOffTime = $createdAt->copy()->setTime(23, 40, 0);
                    
                    if ($createdAt->isSameDay($now) && $createdAt->lte($cutOffTime)) {
                        $details['isVoidEligible'] = true;
                    } else{
                        $details['isVoidEligible'] = false;
                    }
                } catch (\Throwable $th) {
                    Log::error("Unexpected error happened in Mobile/BookingController", [
                        "Error" => $th
                    ]);
                    return response()->json(['message' => "Something went wrong, check backend log"], 500);
                }
                return response()->json($details, 200);
                break;
            
            default:
                # code...
                Log::error("Invalid booking type. Expects CASH, XENDIT string", [
                    "Booking ID" => $booking->id,
                    "Booking type in question" => $booking->transaction->payment_type,
                ]);
                return response()->json(['message' => "Something went wrong"], 500);
                break;
        }

    


    }

    public function refundBooking(Request $request, Booking $booking){
        // Verify if user has a booking
        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }
        
        // Check if the user in request owns the booking or is an owner/manager
        // if ($booking->user_id != $request->user()->id){
        //     return response()->json(['message' => "Unauthorized access"], 401);
        // }

        

        $transaction = $booking->transaction;
        // Check if the status of the transaction is SUCCEEDED
        try {
            if (!in_array($transaction->status, ['SUCCEEDED', 'VERIFIED', 'SCANNED'])) {
                return response()->json(['message' => 'Transaction is not permitted to refund'], 400);
            }
    
            // Fetch the xendit id and append "ewc_" to it
            $xenditId = 'ewc_'.$booking->transaction->xendit_product_id;

            // Call the Xendit API to get the charge info
            // Prepare data conditions
            $response = Xendivel::getPayment($xenditId, 'ewallet')->getResponse();
    
            $createdAt = Carbon::parse($response->created)->timezone('Asia/Manila');
            $now = Carbon::now()->timezone('Asia/Manila');
            // Xendit cutoff time for voiding charge
            $cutOffTime = $createdAt->copy()->setTime(23, 40, 0); 
            
            // Log::info('Date info', ['Xendit created_at' => $createdAt, 'now' => $now]);
            // Log::info('Xendit response:', [$response]);
    
    
            // Void the charge, Check if the created_at is currently in the same day and is before 23:50:00.
            if ($createdAt->isSameDay($now) && $createdAt->lte($cutOffTime)) {
                $response = Xendivel::void($xenditId)->getResponse();
                Log::info('Void response:', [$response]);
            } else{
                $response = Xendivel::getPayment($xenditId, 'ewallet')
                    ->refund((int) $transaction->price * 0.9)
                    ->getResponse();
                Log::info(`Refund response:` . $transaction->price, [$response]);
            }
            $booking->status = "CANCELLED";
            $transaction->status = "REFUND_PENDING";
            $transaction->save();
            $booking->save();

            return response()->json($booking, 201);
        } catch (\Throwable $th) {
            //throw $th;
            Log::info($th);
            return response()->json(['message' => 'Something went wrong'], 400);
        }


    }

    public function cancelBooking(Request $request, Booking $booking){
        $transaction = $booking->transaction;

        // Verify if user has a booking
        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        // Check if the status of the booking is CANCELLED
        if ($booking->status === 'CANCELLED') {
            return response()->json(['message' => 'Booking is already cancelled'], 400);
        }

        // Update the status of the booking to CANCELLED
        $booking->update([
            'status' => 'CANCELLED',
        ]);
        
        // Update the status of the booking to CASH_CANCELLED
        $transaction->update([
            'status' => 'CASH_CANCELLED',
        ]);

        $booking->save();
        $transaction->save();
        

        return response()->json($booking, 200);
    }

    public function rescheduleBooking(Request $request, Booking $booking){
        // Verify if the booking exists

        $validated = $request->validate([
            'booking_type' => 'required',
            'check_in' => 'required',
        ]);

        $booking->update([
            'booking_type' => $validated['booking_type'],
            'check_in' => Carbon::parse($validated['check_in'])->timezone('Asia/Manila')->format('Y-m-d'),
            'check_out' => Carbon::parse($validated['check_in'])->addDay(1)->timezone('Asia/Manila')->format('Y-m-d'),
        ]);

        $booking->save();

        return response()->json($booking, 200);
    }

    public function bookingAction(Request $request, Booking $booking){
        try {
            //code...
            if (!$booking) {
                return response()->json(['message' => 'Booking not found'], 404);
            }
            switch ($request->action) {
                case 'confirm':
                    # code...
                    $booking->status = 'VERIFIED';
                    $booking->transaction->status = 'VERIFIED';
                    break;

                case 'cancel':
                    # code...
                    $booking->status = 'CANCELLED';
                    break;
                default:
                    return response()->json(['message' => 'Something went wrong', 'error', 'Action not acceptable'], 500);
                    break;
                }
            $booking->transaction->save();
            $booking->save();
            return response()->json(['message' => 'Booking status updated']);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something went wrong', 'error' => $th], 500);
        }
    }


    function showVerifiedBookings(Request $request, $page){
        // Get all bookings
        $bookings = [];
        // Check if $page is number
        if (is_numeric($page) && $page > 0) {
            $bookings = Booking::whereIn('status', ['VERIFIED'] )->orderBy('check_in', 'asc')->paginate($page);

            // $bookings = Booking::whereHas('transaction', function ($query) {
            //     $query->where('status', 'VERIFIED');
            // })->orderBy('check_in', 'asc')->paginate($page);
        } else {
            $bookings = Booking::all();
        }

        // Return all bookings with successful bookingresource
        return response()->json($bookings, 200);
    }

    function showCurrentMonthBookings(Request $request, $page){
        // Get all bookings
        $bookings = [];
        // Check if $page is number
        $bookings = Booking::whereIn('status', ['VERIFIED'])->whereMonth('updated_at', Carbon::now()->month)->orderBy('check_in', 'asc')->paginate($page);
        // Return all bookings with successful bookingresource
        return response()->json($bookings, 200);
    }

    function showPreviousMonthBookings(Request $request, $page){
        // Get all bookings
        $bookings = [];
        // Check if $page is number
        $bookings = Booking::whereIn('status', ['VERIFIED'])->whereMonth('updated_at', Carbon::now()->subMonth()->month)->orderBy('check_in', 'asc')->paginate($page);
        // Return all bookings with successful bookingresource
        return response()->json($bookings, 200);
    }

    function showCashOnlyCurrentMonthBookings(Request $request, $page){
        // Get all bookings
        $bookings = [];
        // Check if $page is number
        $bookings = Booking::WhereHas('transaction', function ($query) {
            $query->where('payment_type', 'CASH') && $query->where('status', 'VERIFIED');
        })->whereMonth('updated_at', Carbon::now()->month)->orderBy('check_in', 'asc')->paginate($page);
        // Return all bookings with successful bookingresource
        return response()->json($bookings, 200);
    }

    function showEWalletOnlyCurrentMonthBookings(Request $request, $page){
        // Get all bookings
        $bookings = [];
        // Check if $page is number
        $bookings = Booking::WhereHas('transaction', function ($query) {
            $query->where('payment_type', 'XENDIT') && $query->whereIn('status', ['VERIFIED']);
        })->whereMonth('updated_at', Carbon::now()->month)->orderBy('check_in', 'asc')->paginate($page);
        // Return all bookings with successful bookingresource
        return response()->json($bookings, 200);
    }

    function showCurrentMonthVerifiedBookings(Request $request, $page){
        // Get all bookings
        $bookings = [];
        // Check if $page is number
        $bookings = Booking::whereIn('status', ['VERIFIED'])->orderBy('check_in', 'asc')->paginate($page);
        // Return all bookings with successful bookingresource
        return response()->json($bookings, 200);
    }

    function showCurrentMonthCancelledBookings(Request $request, $page){
        // Get all bookings
        $bookings = [];
        // Check if $page is number
        $bookings = Booking::whereIn('status', ['CANCELLED'])->orderBy('check_in', 'asc')->paginate($page);
        // Return all bookings with successful bookingresource
        return response()->json($bookings, 200);
    }

    function showScannedBookings(Request $request, $page){
        // Get all bookings
        $bookings = [];
        // Check if $page is number
        $bookings = Booking::whereIn('status', ['SCANNED'])->orderBy('check_in', 'asc')->paginate($page);
        // Return all bookings with successful bookingresource
        return response()->json($bookings, 200);
    }

}
