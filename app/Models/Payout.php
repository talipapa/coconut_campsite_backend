<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        "account_name",
        "account_number",
        "amount",
        "account_type",
        "currency",
        "reference_id",
        "status",
        "business_id"
    ];
}
