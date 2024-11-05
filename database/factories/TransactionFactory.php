<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create()->id,
            'booking_id' => Booking::factory()->create()->id,
            'price' => $this->faker->randomFloat(2, 0, 100),
            'status' => 'CASH_PENDING',
            'payment_type' => $this->faker->randomElement(['XENDIT', 'CASH']),
            'xendit_product_id' => $this->faker->name,
        ];
    }
}
