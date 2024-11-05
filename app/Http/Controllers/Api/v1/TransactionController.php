<?php

namespace App\Http\Controllers\Api\v1;

use App\CustomVendors\Xendivel;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\TransactionResource;
use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\CampManager;
use App\Models\Manager;
use Exception;
use GlennRaya\Xendivel\Xendivel as OrigXendivel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{


    // Admin should be able to view all transactions
    //
    public function transactionListAll()
    {
        Gate::authorize('viewAnyAdmin', Transaction::class);
        return TransactionResource::collection(Transaction::all());
    }

    // Campsite managers should only be able to view transactions related to their campsite
    public function viewOnlyCampsiteTransaction(){
        Gate::authorize('viewAnyCampsiteTransaction', Transaction::class);

        $campsiteId = Manager::where('user_id', Auth::user()->id)->first()->campsite_id;
        $campsiteTransactions = Transaction::where('campsite_id', $campsiteId)->get();
        
        return TransactionResource::collection($campsiteTransactions);
    }

    
    // Users must be only able to see their own transactions
    //
    public function index()
    {
        $authenticatedUser = Auth::user();
        return TransactionResource::collection(Transaction::where('user_id', $authenticatedUser->id)->get());
    }

    public function findXenditTransaction(Request $request, $transactionId)
    {

        $response = OrigXendivel::getPayment('ewc_'.$transactionId, 'ewallet')->getResponse();
        // Log::info("Xendit response", [$response]);
        
        return response()->json($response);
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required',
            'price' => 'required',
            'paymentMethod' => 'required',
            'payment_type' => 'required',
        ]);
        



        $transaction = Transaction::where('booking_id', $validated['booking_id'])->first();
        $transaction->payment_type = $validated['payment_type'];
        $transaction->price = $validated['price'];
        $transaction->price = $validated['price'];
        $transaction->save();
        // if (!$transaction) {
        //     // Create transaction
        //     $transaction = Transaction::create([
        //         'user_id' => Auth::user()->id,
        //         'booking_id' => $validated['booking_id'],
        //         'price' => $validated['price'],
        //         'payment_type' => $validated['payment_type'],
        //     ]);  
        // }
        // Log::info($validated['booking_id']);
        // Log::info("Transaction", [$transaction]);
        // if ($transaction) {
        //     $transaction->status = 'CANCELLED';
        //     $transaction->save();
        //     // $response = OrigXendivel::getPayment('ewc_'.$transaction->xendit_product_id, 'ewallet')->getResponse();
        //     // Log::info("Xendit response", [$response]);
        //     $transaction->delete();
        // }
        
        switch ($validated['payment_type']) {
            case 'XENDIT':
                // Create transaction
                // $transaction = Transaction::create([
                //     'user_id' => Auth::user()->id,
                //     'booking_id' => $validated['booking_id'],
                //     'price' => $validated['price'],
                //     'payment_type' => $validated['payment_type'],
                // ]);  
                
                $transaction = Transaction::where('booking_id', $validated['booking_id'])->first();

                try {
                    //code...
            
                    // Prepare payment request body

                    $xenditRequest = [
                        'reference_id' => $transaction->id,
                        'amount' => floatval($validated['price']),
                        'currency' => 'PHP',
                        'checkout_method' => 'ONE_TIME_PAYMENT',
                        'channel_code' => $validated['paymentMethod'],
                        'channel_properties' => [
                            'success_redirect_url' => config('xendivel.xendit_success_url'),
                            'failure_redirect_url' => config('xendivel.xendit_failure_url'),
                            'cancel_redirect_url' => config('xendivel.xendit_cancel_url'),
                        ],
                    ];

                    // Error simulation
                    // $xenditRequest = [
                    //     'reference_id' => $transaction->id,
                    //     'amount' => 20105,
                    //     'currency' => 'PH',
                    //     'checkout_method' => 'ONE_TIME_PAYMENT',
                    //     'channel_code' => "PH_SHOpP",
                    //     'channel_properties' => [
                    //         'success_redirect_url' => env('XENDIT_SUCCESS_URL'),
                    //         'failure_redirect_url' => env('XENDIT_FAILURE_URL'),
                    //     ],
                    // ];
        
                    // Send payment request to XENDIT
                    $response = Xendivel::payWithEwallet($xenditRequest)->getResponse();                    
                    Log::info("Xendit response", [$response->id]);
                    $transaction->xendit_product_id = substr($response->id, 4);
                    $transaction->save();
                    return response()->json([
                        'message' => '[Online Payment] Booking Created Successfully',
                        'data' => $response
                    ], 201);
                    
                } catch (\Throwable $th) {
                    Log::info($th);
                    $transaction->delete();
                    return response()->json($th->getMessage(), 500);
                }
                break;
        
            case 'CASH':
                # code...
                // $transaction = Transaction::create([
                //     'user_id' => Auth::user()->id,
                //     'booking_id' => $validated['booking_id'],
                //     'price' => $validated['price'],
                //     'payment_type' => $validated['payment_type'],
                //     'status' => 'CASH_PENDING'
                // ]);
                
                $transaction = Transaction::where('booking_id', $validated['booking_id'])->first();
                $booking = Booking::where('id', $transaction->booking_id)->first();
                $transaction->status = 'CASH_PENDING';
                $booking->status = 'CASH_PENDING';
                $transaction->save();
                $booking->save();

    
                return response()->json([
                    'message' => '[Online Payment] Transaction Created Successfully',
                    'data' => new TransactionResource($transaction)
                ], 201);

                break;
            default:
                return response()->json(['message' => 'Invalid payment type'], 400);
        }
    } 

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        $authenticatedUser = Auth::user();
        if ($transaction->user_id !== $authenticatedUser->id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $booking = Booking::where('id', $transaction->booking_id)->first();

        $transactionJson = new TransactionResource($transaction);
        $BookingJson = $booking;

        return [
            'message' => 'Transaction found',
            'transaction' => $transactionJson,
            'booking' => $BookingJson,

        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        Gate::authorize('update', $transaction);
        // Validate
        $validated = $request->validate([
            'price' => 'required',
            'status' => 'required',
            'payment_type' => 'required',
            'xendit_transaction_id' => 'required',
        ]);

    
        // Find data
        $transactionData = Transaction::find($transaction->id);
        if (!$transaction) {
            return response()->json(['message' => 'Campsite not found'], 404);
        }

        $oldData = $transactionData;

        // Update data
        $transaction->update([
            'price' => $validated['price'],
            'status' => $validated['status'],
            'payment_type' => $validated['payment_type'],
            'xendit_transaction_id' => $validated['xendit_transaction_id']
        ]);

        $transaction->save();
        // Return response
        $transactionDataJson = new TransactionResource($transaction);
        return response()->json([
            'message' => 'Campsite updated successfully',
            'old_data' => $oldData,
            'new_data' => $transactionDataJson], 200);
           
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        Gate::authorize('delete', $transaction);
        $oldData = $transaction;
        $transaction->delete();
        return response()->json(
            ['message' => 'Campsite tag deleted successfully', 
            'additional-info' => "Campsite tag" . $oldData->name . ' has been deleted',]
            , 200);
    }
}
