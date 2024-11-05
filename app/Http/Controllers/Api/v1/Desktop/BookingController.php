<?php

namespace App\Http\Controllers\Api\v1\Desktop;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\SuccessfulBookingResource;
use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function dashboardSummary(Request $request){
        try {
            // total earnings of current year
            $totalYearEarnings = Transaction::whereYear('updated_at', Carbon::now()->year)
            ->where('status', 'SUCCEEDED')
            ->sum('price');

            // total earnings this month
            $totalMonthEarnings = Transaction::whereYear('updated_at', Carbon::now()->year)
            ->whereMonth('updated_at', Carbon::now()->month)
            ->where('status', 'SUCCEEDED')
            ->sum('price');

            // total earnings from previous month
            $startOfPreviousMonth = Carbon::now()->startOfMonth()->subMonth();
            $endOfPreviousMonth = Carbon::now()->subMonth()->endOfMonth();

            $totalPreviousMonthEarnings = Transaction::whereBetween('updated_at', [$startOfPreviousMonth, $endOfPreviousMonth])->where('status', 'SUCCEEDED')->sum('price');

            

            // cash revenue this month
            $cashRevenueThisMonth = Transaction::whereYear('updated_at', Carbon::now()->year)
            ->whereMonth('updated_at', Carbon::now()->month)
            ->where('status', 'SUCCEEDED')
            ->where('payment_type', 'CASH')
            ->sum('price');

            // e-payment revenue this month
            $ePaymentRevenueThisMonth = Transaction::whereYear('updated_at', Carbon::now()->year)
            ->whereMonth('updated_at', Carbon::now()->month)
            ->where('status', 'SUCCEEDED')
            ->where('payment_type', 'XENDIT')
            ->sum('price');

            // success booking this month
            $successBookingThisMonth = Transaction::whereYear('updated_at', Carbon::now()->year)
            ->whereMonth('updated_at', Carbon::now()->month)
            ->where('status', 'SUCCEEDED')
            ->count();

            // cancelled booking this month
            $cancelledBookingThisMonth = Transaction::whereYear('updated_at', Carbon::now()->year)
            ->whereMonth('updated_at', Carbon::now()->month)
            ->whereIn('status', ['CANCELLED', 'VOIDED', 'REFUNDED', 'FAILED'])
            ->count();
    
            return response()->json([
                'totalYearEarnings' => $totalYearEarnings,
                'totalMonthEarnings' => $totalMonthEarnings,
                'totalPreviousMonthEarnings' => $totalPreviousMonthEarnings,
                'cashRevenueThisMonth' => $cashRevenueThisMonth,
                'ePaymentRevenueThisMonth' => $ePaymentRevenueThisMonth,
                'successBookingThisMonth' => $successBookingThisMonth,
                'cancelledBookingThisMonth' => $cancelledBookingThisMonth
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something went wrong', 'error' => $th], 500);
        }
    }

    public function fetchScannedBooking(Request $request){
        try {
            //code...
            // sort by check in date
            $perPage = $request->query('per_page', 10);
            $scannedBooking = Booking::whereIn('status', ['SCANNED', 'PAID', 'PENDING'])->paginate($perPage);
            return response()->json($scannedBooking);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something went wrong', 'error' => $th], 500);
        }
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
                    $booking->transaction->status = 'SUCCEEDED';
                    break;

                case 'cancel':
                    # code...
                    $booking->status = 'FAILED';
                    $booking->transaction->status = 'FAILED';
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

    public function fetchSuccessfulBooking(Request $request){
        try {
            //code...
            // sort by check in date
            $perPage = $request->query('per_page', 10);
            $scannedBooking = Booking::where('status', 'VERIFIED')->paginate($perPage);
            return response()->json($scannedBooking);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something went wrong', 'error' => $th], 500);
        }
    }

    public function fetchAllBooking(Request $request){
        try {
            //code...
            // sort by check in date
            $perPage = $request->query('per_page', 10);
            $scannedBooking = Booking::paginate($perPage);
            return response()->json($scannedBooking);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something went wrong', 'error' => $th], 500);
        }
    }

    public function fetchSingleBooking(Request $request, Booking $booking){
        try {
            //code...
            if (!$booking) {
                return response()->json(['message' => 'Booking not found'], 404);
            }
            return response()->json([
                'booking' => $booking,
                'transaction' => $booking->transaction
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something went wrong', 'error' => $th], 500);
        }
    }
}
