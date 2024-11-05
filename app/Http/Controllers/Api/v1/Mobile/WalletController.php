<?php

namespace App\Http\Controllers\Api\v1\Mobile;

use App\CustomVendors\Xendivel;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payout;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{

    public function displayWallet(Request $request) {
        $wallet = [
            'XENDIT' => '',
            'VERIFIED_CASH' => '', 
        ];

        $response = Xendivel::getBalance()->getResponse();
        $decodedResponse = json_decode(json_encode($response), true);
        $wallet['XENDIT'] = $decodedResponse['balance'];

        $fetchVerifiedCash = Transaction::where('status', 'VERIFIED')->get()->sum(function($transaction){
            return $transaction->price;
        });

        Log::alert($fetchVerifiedCash);
        $wallet['VERIFIED_CASH'] = $fetchVerifiedCash;

        return $wallet;
    }

    public function createPayout(Request $request){
        $validated = $request->validate([
            'account_holder_name' => 'required|max:20',
            // Character should be 11 exact
            'account_number' => 'required|numeric|digits:11',
            'amount' => 'required|numeric|min:10|max:20000',
        ]);

        $payout = Payout::create([
            "account_name" => $validated['account_holder_name'],
            "account_number" => $validated['account_number'],
            "amount" => $validated['amount'],
            "business_id" => config('xendivel.business_id'),
        ]);

        try {
            //code...
            $response = Xendivel::sendDisbursement([
                'reference' => $payout->id,
                'disbursements' => [
                    [
                        'amount' => (int) $payout->amount,
                        'bank_code' => 'PH_GCASH',
                        'bank_account_name' => $payout->account_name,
                        'bank_account_number' => $payout->account_number,
                        'description' => 'Payout for '.$payout->account_name,
                        'email_to' => [$request->user()->email]
                    ]
                ]
            ])->getResponse();
            $payout->save();
            return response()->json([
                'message' => [
                    'status' => 'success',
                    'message' => 'Payout request sent successfully',
                    'email_to' => $request->user()->email
                ]
            ], 200);

        } catch (\Throwable $th) {
            $payout->delete();
            return response()->json([
                'message' => [
                    'status' => 'error',
                    'message' => 'Payout request failed',
                    'email_to' => $request->user()->email
                ]
            ], 400);
        }



    }
}

// curl https://api.xendit.co/balance -X GET \
// -u xnd_development_O46JfOtygef9kMNsK+ZPGT+ZZ9b3ooF4w3Dn+R1k+2fT/7GlCAN3jg==: