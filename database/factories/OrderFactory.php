<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => fake()->unique()->bothify('INV-####'),
            'customer_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'package' => fake()->randomElement(['Essential', 'Signature', 'Bespoke']),
            'event_type' => fake()->randomElement(['Pernikahan', 'Ulang Tahun', 'Corporate']),
            'total' => fake()->randomElement([99000, 179000, 399000]),
            'status' => 'waiting',
            'payment_status' => 'unpaid',
        ];
    }
}
