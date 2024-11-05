<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'transaction_id',
        'xendit_refund_id',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
