<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'transaction_id' => $this->transaction_id,
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
            'created_at' => $this->created_at,
            'transactionStatus' => $this->transaction->status,
        ];
    }
}
