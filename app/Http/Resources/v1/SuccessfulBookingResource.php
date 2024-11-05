<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuccessfulBookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->first_name. " ". $this->last_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'tel_number' => $this->tel_number,
            'adult_count' => $this->adultCount,
            'child_count' => $this->childCount,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'booking_type' => $this->booking_type,
            'tent_pitching_count' => $this->tent_pitching_count,
            'bonfire_kit_count' => $this->bonfire_kit_count,
            'is_cabin' => $this->is_cabin,
            'note' => $this->note,
            'status' => $this->status,
            'transaction_id' => $this->transaction ?  $this->transaction->id : null,
            'transactionType' => $this->transaction ?  $this->transaction->payment_type : null,
            'price' => $this->transaction ? $this->transaction->price : null,
            'transactionStatus' => $this->transaction ? $this->transaction->status : null,
            'xendit_id' => $this->transaction ? $this->transaction->xendit_id : null,
            'created_at' => $this->created_at,
        ];
    }
}
