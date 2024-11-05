<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
        'user_id' => $this->user_id,
        'booking_id' => $this->booking_id,
        'price' => $this->price,
        'status' => $this->status,
        'payment_type' => $this->payment_type,
        'xendit_product_id' => $this->xendit_product_id
        ];
    }
}
