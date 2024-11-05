<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Price;
use App\Models\Transaction;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Price::factory()->create([
            'name' => 'adult',
            'price' => 100.00,
        ]);
        Price::factory()->create([
            'name' => 'child',
            'price' => 50.00,
        ]);
        Price::factory()->create([
            'name' => 'tent_pitch',
            'price' => 70.00,
        ]);
        Price::factory()->create([
            'name' => 'bonfire',
            'price' => 150.00,
        ]);
        Price::factory()->create([
            'name' => 'cabin',
            'price' => 650.00,
        ]);
        
        // User::factory(10)->create();
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        // Transaction::factory(100)->create();

    }
}
