<?php
namespace App\CustomVendors;

use Exception;
use GlennRaya\Xendivel\XenditApi;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Xendivel extends XenditApi{

    public static $chargeResponse;


    private static function generateAuthToken(): string
    {
        return base64_encode(config('xendivel.secret_key').':');
    }

    public function getResponse()
    {
        return json_decode(self::$chargeResponse);
    }

    private static function baseApi(string $method, string $url, array $payload = []): Response {
        // Check if the secret key is set in .env file.
        if (empty(config('xendivel.secret_key'))) {
            throw new Exception('Your Xendit secret key (XENDIT_SECRET_KEY) is not set from your .env file.');
        }
        
        // Perform Xendit API call with proper authentication token setup.

        if (count($payload) !== 0){
            $response = Http::withHeaders([
                'Authorization' => 'Basic '.self::generateAuthToken(),
                'X-IDEMPOTENCY-KEY' => isset($payload['idempotency']) ? $payload['idempotency'] : '',
            ])
                ->$method("https://api.xendit.co/{$url}", $payload);
        } else{
            $response = Http::withHeaders([
                'Authorization' => 'Basic '.self::generateAuthToken(),
                'X-IDEMPOTENCY-KEY' => isset($payload['idempotency']) ? $payload['idempotency'] : '',
            ])
                ->$method("https://api.xendit.co/{$url}");
        }

        // Throw an exception when the request failed.
        if ($response->failed()) {
            throw new Exception($response);
        }
        self::$chargeResponse = $response;
        return $response;
    }

    public static function payWithEwallet($payload): self
    {
        if (config('xendivel.auto_id')
            ? $payload['reference_id'] = Str::orderedUuid()
            : $payload['reference_id']) {
        }

        // $payload = $payload->toArray();

        $response = XenditApi::api('post', '/ewallets/charges', $payload);

        if ($response->failed()) {
            throw new Exception($response);
        }
        self::$chargeResponse = $response;
        return new self();
    }

    public static function getBalance(): self{

        $response = self::baseApi('get', '/balance');

        if ($response->failed()) {
            throw new Exception($response);
        }

        return new self();
    }

    // Checkpoint
    public static function sendDisbursement($payload): self{

        $response = self::baseApi(('post'), "/batch_disbursements", $payload);
        
        if ($response->failed()){
            throw new Exception($response);
        }

        return new self();
    }
}