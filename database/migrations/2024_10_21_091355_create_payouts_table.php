<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string("account_name");
            $table->string("account_number");
            $table->string("amount")->default(0);
            $table->string("account_type")->default("MOBILE_NO");
            $table->string("currency")->default("PHP");
            $table->string('status')->default('PENDING');
            $table->string("business_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
