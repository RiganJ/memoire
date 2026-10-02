<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_method_id' => PaymentMethod::factory(),
            'transaction_number' => fake()->unique()->bothify('PAY-####'),
            'amount' => fake()->randomElement([99000, 179000, 399000]),
            'status' => 'pending',
            'paid_at' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
