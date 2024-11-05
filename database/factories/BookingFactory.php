<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create();
        return [
            "user_id" => $user->id,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "email" => $user->email,
            "tel_number" => "9925606298",
            "adultCount" => rand(1, 10),
            "childCount" => rand(0, 10),
            "check_in" => Carbon::now()->addDays(rand(1, 30)),
            "check_out" => Carbon::now()->subDays((rand(1, 30)))->addDays(rand(1, 100)),
            "booking_type" => $this->faker->randomElement(["overnight", "daytour"]),
            "tent_pitching_count" => rand(1, 10),
            "bonfire_kit_count" => rand(1, 10),
            "is_cabin" => false,
            "note" => "test",
            "status" => "SCANNED"
        ];
    }
}
