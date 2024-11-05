<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory, HasUlids;

    protected $casts = [
        'is_cabin' => 'boolean'
    ];
    
    protected $fillable = [
        'user_id',
        'full_name',
        'first_name',
        'last_name',
        'email',
        'tel_number',
        'adultCount',
        'childCount',
        'check_in',
        'check_out',
        'booking_type',
        'tent_pitching_count',
        'bonfire_kit_count',
        'is_cabin',
        'note',
        'status',
        'payment_type'
    ];

    protected $appends = ['full_name', 'payment_type'];

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getPaymentTypeAttribute()
    {
        return $this->transaction ? $this->transaction->payment_type : null;
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(){
        return $this->hasOne(Transaction::class);
    }
}
